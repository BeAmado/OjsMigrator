<?php

use BeAmado\OjsMigrator\Test\FunctionalTest;
use BeAmado\OjsMigrator\Entity\IssueHandler;
use BeAmado\OjsMigrator\Registry;
use BeAmado\OjsMigrator\Test\OjsScenarioHandler;

// interfaces
use BeAmado\OjsMigrator\Test\StubInterface;

// traits 
use BeAmado\OjsMigrator\Test\TestStub;

// mocks
use BeAmado\OjsMigrator\Test\JournalMock;
use BeAmado\OjsMigrator\Test\IssueMock;
use BeAmado\OjsMigrator\Test\SectionMock; // for the custom_section_orders data

class IssueHandlerTest extends FunctionalTest implements StubInterface
{
    protected static function createTheIssueFiles($issue)
    {
        $issue->get('files')->forEachValue(function($issueFile) {
            Registry::get('FileHandler')->write(
                Registry::get('IssueFileHandler')->formFilePathInEntitiesDir(
                    $issueFile->get('file_name')->getValue()
                ),
                'This is the issue with original file name '
                    . $issueFile->get('file_name')->getValue()
            );
        });
    }

    public static function setUpBeforeClass($args = array(
        'createTables' => array(
            'journals',
            'sections',
            'issues',
            'issue_settings',
            'issue_galleys',
            'issue_galley_settings',
            'issue_files',
            'custom_issue_orders',
            'custom_section_orders',
        ),
    )) : void {
        parent::setUpBeforeClass($args);

        $journal = (new JournalMock())->getTestJournal();
        Registry::get('EntityHandler')->createOrUpdateInDatabase($journal);

        foreach(array(
            'sciences',
            'sports',
        ) as $section) {
            Registry::get('EntityHandler')->createOrUpdateInDatabase(
                (new SectionMock())->getSection($section)
            );
        }

        $fsm = Registry::get('FileSystemManager');

        $fsm->createDir(Registry::get('IssueHandler')->getJournalIssuesDir(
            Registry::get('DataMapper')->getMapping(
                'journals',
                $journal->getId()
            )
        ));

        foreach(array(
            (new IssueMock())->getRWC2015Issue(),
            (new IssueMock())->getRWC2011Issue(),
        ) as $issue) {
            $fsm->createDir($fsm->formPath(array(
                //Registry::get('IssueHandler')->getJournalIssuesDir($journal),
                Registry::get('EntityHandler')->getEntityDataDir('issues'),
                $issue->getId(),
            )));

            self::createTheIssueFiles($issue);
        }
    }

    public function getStub()
    {
        return new class extends IssueHandler {
            use TestStub;
        };
    }

    public function __construct()
    {
        parent::__construct();
        $this->issueMock = new IssueMock();
        $this->scenario = new OjsScenarioHandler();
    }

    public function testCanGetTheDirectoryWhereTheIssueFilesAreStored()
    {
        $testJournal = (new JournalMock())->getTestJournal();
        $this->assertSame(
            Registry::get('FileSystemManager')->formPath(array(
                $this->scenario->getOjsFilesDir(),
                'journals',
                $testJournal->getId(),
                'issues',
            )),
            Registry::get('IssueHandler')->getJournalIssuesDir($testJournal)
        );
    }

    public function testTheFilesDirExists()
    {
        $this->assertTrue(
            Registry::get('FileSystemManager')->dirExists(
                $this->scenario->getOjsFilesDir()
            )
        );
    }

    protected function createRWC2015Issue()
    {
        return $this->issueMock->getRWC2015Issue();
    }

    protected function createRWC2011Issue()
    {
        return $this->issueMock->getRWC2011Issue();
    }

    public function testCanCreateTheRugbyWorldCup2015MockedIssue()
    {
        $issue = $this->createRWC2015Issue();
        $testJournal = (new JournalMock())->getTestJournal();
        $sportsSection = (new SectionMock())->getSportsSection();
        $sciencesSection = (new SectionMock())->getSciencesSection();

        $this->assertSame(
            '1-1-1-1',
            implode('-', array(
                (int) $this->areEqual(
                    $issue->get('journal_id')->getValue(),
                    $testJournal->get('journal_id')->getValue()
                ),
                (int) $this->areEqual(
                    $issue->get('custom_order')->get('journal_id')->getValue(),
                    $testJournal->get('journal_id')->getValue()
                ),
                (int) $this->areEqual(
                    $issue->get('custom_section_orders')->get(0)
                          ->get('section_id')->getValue(),
                    $sportsSection->get('section_id')->getValue()
                ),
                (int) $this->areEqual(
                    $issue->get('custom_section_orders')->get(1)
                          ->get('section_id')->getValue(),
                    $sciencesSection->get('section_id')->getValue()
                ),
            ))
        );

    }

    public function testCanRegisterTheRugbyWorldCup2015Issue()
    {
        $issue = $this->createRWC2015Issue();

        $registered = $this->getStub()->callMethod(
            'registerIssue',
            $issue
        );

        $fromDb = Registry::get('IssuesDAO')->read(array(
            'issue_id' => Registry::get('DataMapper')->getMapping(
                'issues',
                $issue->getId()
            )
        ));

        $this->assertSame(
            '1-1-1',
            implode('-', array(
                (int) $registered,
                $fromDb->length(),
                (int) Registry::get('EntityHandler')->areEqual(
                    $issue,
                    $fromDb->get(0),
                    array('journal_id') // not compare the journal_id
                ),
            ))
        );
    }

    public function testCanImportAnIssueSetting()
    {
        $issueSetting = $this->createRWC2015Issue()->get('settings')->get(0);

        $imported = $this->getStub()->callMethod(
            'importIssueSetting',
            $issueSetting
        );

        $fromDb = Registry::get('IssueSettingsDAO')->read(array(
            'issue_id' => Registry::get('DataMapper')->getMapping(
                'issues',
                $issueSetting->get('issue_id')->getValue()
            )
        ));

        $this->assertSame(
            '1-1-1',
            implode('-', array(
                (int) $imported,
                $fromDb->length(),
                (int) Registry::get('EntityHandler')->areEqual(
                    $fromDb->get(0),
                    $issueSetting,
                    array('issue_id')
                )
            ))
        );
    }

    public function testCanImportAnIssueFile()
    {
        $issueFile = $this->createRWC2015Issue()->get('files')->get(0);

        $imported = $this->getStub()->callMethod(
            'importIssueFile',
            $issueFile
        );

        $fromDb = Registry::get('IssueFilesDAO')->read(array(
            'file_id' => Registry::get('DataMapper')->getMapping(
                'issue_files',
                $issueFile->get('file_id')->getValue()
            )
        ));

        $filename = $fromDb->get(0)->get('file_name')->getValue();
        $dir = Registry::get('FileSystemManager')->parentDir(
            Registry::get('IssueHandler')->formIssueFilenameFullpath(
                $filename
            )
        );
        $expectedContent = 'This is the issue with original file name '
            . $issueFile->get('file_name')->getValue();

        $fileslist = Registry::get('FileSystemManager')->listdir($dir);

        $this->assertSame(
            '1-1-1-1-1-1',
            implode('-', array(
                (int) $imported,
                (int) $fromDb->length(),
                (int) Registry::get('FileSystemManager')->dirExists($dir),
                (int) count($fileslist),
                (int) $this->areEqual(
                    $filename, 
                    basename($fileslist[0])
                ),
                (int) $this->areEqual(
                    $expectedContent,
                    Registry::get('FileHandler')->read($fileslist[0])
                ),
            ))
        );
    }

    public function testCanImportAnIssueGalley()
    {
        $issueGalley = $this->createRWC2015Issue()->get('galleys')->get(0);

        $imported = $this->getStub()->callMethod(
            'importIssueGalley',
            $issueGalley
        );

        $fromDb = Registry::get('IssueGalleysDAO')->read(array(
            'galley_id' => Registry::get('DataMapper')->getMapping(
                'issue_galleys',
                $issueGalley->get('galley_id')->getValue()
            )
        ));

        Registry::get('EntityHandler')->setMappedData($issueGalley, array(
            'issue_galleys' => 'galley_id',
            'issues' => 'issue_id',
            'issue_files' => 'file_id',
        ));

        $this->assertSame(
            '1-1-1-1',
            implode('-', array(
                (int) $imported,
                (int) $fromDb->length(),
                (int) $this->areEqual(
                    $fromDb->get(0)->getId(),
                    $issueGalley->get('galley_id')->getValue()
                ),
                (int) Registry::get('EntityHandler')->areEqual(
                    $fromDb->get(0),
                    $issueGalley
                ),
            ))
        );
    }

    public function testCanImportTheCustomIssueOrder()
    {
        $customOrder = $this->createRWC2015Issue()->get('custom_order');

        $imported = $this->getStub()->callMethod(
            'importCustomIssueOrder',
            $customOrder
        );

        $fromDb = Registry::get('CustomIssueOrdersDAO')->read(array(
            'issue_id' => Registry::get('DataMapper')->getMapping(
                'issues',
                $customOrder->get('issue_id')->getValue()
            )
        ));

        Registry::get('EntityHandler')->setMappedData($customOrder, array(
            'issues' => 'issue_id',
            'journals' => 'journal_id',
        ));

        $this->assertSame(
            '1-1-1',
            implode('-', array(
                (int) $imported,
                (int) $fromDb->length(),
                (int) Registry::get('EntityHandler')->areEqual(
                    $fromDb->get(0),
                    $customOrder
                ),
            ))
        );
    }

    public function testCanImportACustomSectionOrder()
    {
        $cso = $this->createRWC2015Issue()
                    ->get('custom_section_orders')->get(0);
        
        $imported = $this->getStub()->callMethod(
            'importCustomSectionOrder',
            $cso
        );

        Registry::get('EntityHandler')->setMappedData($cso, array(
            'issues' => 'issue_id',
            'sections' => 'section_id',
        ));

        $fromDb = Registry::get('CustomSectionOrdersDAO')->read($cso);

        $this->assertSame(
            '1-1-1',
            implode('-', array(
                (int) $imported,
                (int) $fromDb->length(),
                (int) Registry::get('EntityHandler')->areEqual(
                    $fromDb->get(0),
                    $cso
                ),
            ))
        );
    }

    /**
     * @depends testCanRegisterTheRugbyWorldCup2015Issue
     * @depends testCanImportAnIssueSetting
     * @depends testCanImportAnIssueFile
     * @depends testCanImportAnIssueGalley
     * @depends testCanImportTheCustomIssueOrder
     * @depends testCanImportACustomSectionOrder
     */
    public function testCanImportTheRugbyWorldCup2015Issue()
    {
        $issue = $this->createRWC2015Issue();

        $imported = Registry::get('IssueHandler')->importIssue($issue);

        $issueId = Registry::get('DataMapper')->getMapping(
            'issues',
            $issue->getId()
        );

        $issuesFromDb = Registry::get('IssuesDAO')->read(array(
            'issue_id' => $issueId,
        ));

        $issueSettingsFromDb = Registry::get('IssueSettingsDAO')->read(array(
            'issue_id' => $issueId,
        ));

        $issueFilesFromDb = Registry::get('IssueFilesDAO')->read(array(
            'issue_id' => $issueId,
        ));

        $issueGalleysFromDb = Registry::get('IssueGalleysDAO')->read(array(
            'issue_id' => $issueId,
        ));

        $ciOrdersFromDb = Registry::get('CustomIssueOrdersDAO')->read(array(
            'issue_id' => $issueId,
        ));

        $csOrdersFromDb = Registry::get('CustomSectionOrdersDAO')->read(array(
            'issue_id' => $issueId,
        ));

        $this->assertSame(
            '1-1-3-1-1-1-2',
            implode('-', array(
                (int) $imported,
                $issuesFromDb->length(),
                $issueSettingsFromDb->length(),
                $issueFilesFromDb->length(),
                $issueGalleysFromDb->length(),
                $ciOrdersFromDb->length(),
                $csOrdersFromDb->length(),
            ))
        );
    }

    /**
     * @depends testCanImportTheRugbyWorldCup2015Issue
     */
    public function testCanImportTheRugbyWorldCup2011Issue()
    {
        $issue = $this->createRWC2011Issue();

        $imported = Registry::get('IssueHandler')->importIssue($issue);

        $issueId = Registry::get('DataMapper')->getMapping(
            'issues',
            $issue->getId()
        );

        $issuesFromDb = Registry::get('IssuesDAO')->read(array(
            'issue_id' => $issueId,
        ));

        $issueSettingsFromDb = Registry::get('IssueSettingsDAO')->read(array(
            'issue_id' => $issueId,
        ));

        $issueFilesFromDb = Registry::get('IssueFilesDAO')->read(array(
            'issue_id' => $issueId,
        ));

        $issueGalleysFromDb = Registry::get('IssueGalleysDAO')->read(array(
            'issue_id' => $issueId,
        ));

        $ciOrdersFromDb = Registry::get('CustomIssueOrdersDAO')->read(array(
            'issue_id' => $issueId,
        ));

        $csOrdersFromDb = Registry::get('CustomSectionOrdersDAO')->read(array(
            'issue_id' => $issueId,
        ));

        $fileslist = Registry::get('FileSystemManager')->listdir(
            
        );

        $this->assertSame(
            '1-1-2-2-2-1-2',
            implode('-', array(
                (int) $imported,
                $issuesFromDb->length(),
                $issueSettingsFromDb->length(),
                $issueFilesFromDb->length(),
                $issueGalleysFromDb->length(),
                $ciOrdersFromDb->length(),
                $csOrdersFromDb->length(),
            ))
        );
    }

    protected function getMappedIssue($alias, $extra = array())
    {
        $issue = null;

        switch(strtolower($alias)) {
            case '2015':
            case 'rwc2015':
                $issue = $this->createRWC2015Issue();
                break;
            case '2011':
            case 'rwc2011':
                $issue = $this->createRWC2011Issue();
                break;
        }

        Registry::get('EntityHandler')->setMappedData($issue, array(
            'issues' => 'issue_id',
            'journals' => 'journal_id',
        ));

        if (empty($extra))
            return $issue;

        // map the issue_settings data
        if (
            in_array('settings', $extra) &&
            $issue->hasAttribute('settings')
        )
            $issue->get('settings')->forEachValue(function($s) {
                Registry::get('EntityHandler')->setMappedData($s, array(
                    'issues' => 'issue_id',
                ));
            });
        
        // map the issue_files data
        if (
            in_array('issue_files', $extra) &&
            $issue->hasAttribute('files')
        )
            $issue->get('files')->forEachValue(function($f) {
                Registry::get('EntityHandler')->setMappedData($f, array(
                    'issues' => 'issue_id',
                    'issue_files' => 'file_id',
                ));

                $f->set(
                    'file_name',
                    $this->getStub()->callMethod(
                        'formNewIssueFilename',
                        array(
                            'issueFile' => $f
                        )
                    )
                );
            });

        // map the issue_galleys data
        if (
            in_array('issue_galleys', $extra) &&
            $issue->hasAttribute('galleys')
        )
            $issue->get('galleys')->forEachValue(function($g) {
                Registry::get('EntityHandler')->setMappedData($g, array(
                    'issues' => 'issue_id',
                    'issue_galleys' => 'galley_id',
                    'issue_files' => 'file_id',
                ));
            });

        // map the custom_issue_orders data
        if (
            in_array('custom_issue_orders', $extra) &&
            $issue->hasAttribute('custom_order')
        )
            Registry::get('EntityHandler')->setMappedData(
                $issue->get('custom_order'),
                array(
                    'issues' => 'issue_id',
                    'journals' => 'journal_id',
                )
            );
        
        // map the custom_section_orders data
        if (
            in_array('custom_section_orders', $extra) &&
            $issue->hasAttribute('custom_section_orders')
        )
            $issue->get('custom_section_orders')->forEachValue(function($o) {
                Registry::get('EntityHandler')->setMappedData($o, array(
                    'issues' => 'issue_id',
                    'sections' => 'section_id',
                ));
            });

        return $issue;
    }

    /**
     * @depends testCanImportTheRugbyWorldCup2015Issue
     */
    public function testCanGetTheIssueSettings()
    {
        $issue = $this->getMappedIssue('rwc2015', array('settings'));

        $issueSettings = $this->getStub()->callMethod(
            'getIssueSettings',
            $issue
        );

        $this->assertSame(
            '1-1',
            implode('-', array(
                (int) $this->areEqual(
                    $issueSettings->length(),
                    $issue->get('settings')->length()
                ),
                (int) Registry::get('ArrayHandler')->areEquivalent(
                    $issueSettings->toArray(),
                    $issue->get('settings')->toArray()
                ),
            ))
        );
    }

    /**
     * @depends testCanImportTheRugbyWorldCup2015Issue
     */
    public function testCanGetTheIssueFiles()
    {
        $issue = $this->getMappedIssue('rwc2015', array('issue_files'));

        $issueFiles = $this->getStub()->callMethod(
            'getIssueFiles',
            $issue
        );

        $this->assertSame(
            '1-1',
            implode('-', array(
                (int) $this->areEqual(
                    $issueFiles->length(),
                    $issue->get('files')->length()
                ),
                (int) Registry::get('ArrayHandler')->areEquivalent(
                    $issueFiles->toArray(),
                    $issue->get('files')->toArray()
                ),
            ))
        );
    }

    /**
     * @depends testCanImportTheRugbyWorldCup2015Issue
     */
    public function testCanGetTheIssueGalleys()
    {
        $issue = $this->getMappedIssue('rwc2015', array('issue_galleys'));

        $issueGalleys = $this->getStub()->callMethod(
            'getIssueGalleys',
            $issue
        );

        $this->assertSame(
            '1-1',
            implode('-', array(
                (int) $this->areEqual(
                    $issueGalleys->length(),
                    $issue->get('galleys')->length()
                ),
                (int) Registry::get('ArrayHandler')->areEquivalent(
                    $issueGalleys->toArray(),
                    $issue->get('galleys')->toArray()
                ),
            ))
        );
    }

    /**
     * @depends testCanImportTheRugbyWorldCup2015Issue
     */
    public function testCanGetTheCustomIssueOrder()
    {
        $issue = $this->getMappedIssue(
            'rwc2015', 
            array('custom_issue_orders')
        );

        $issueOrder = $this->getStub()->callMethod(
            'getCustomIssueOrder',
            $issue
        );
        
        $this->assertSame(
            '1',
            implode('-', array(
                (int) Registry::get('EntityHandler')->areEqual(
                    $issueOrder,
                    $issue->get('custom_order')
                ),
            ))
        );
    }

    /**
     * @depends testCanImportTheRugbyWorldCup2015Issue
     */
    public function testCanGetTheCustomSectionOrders()
    {
        $issue = $this->getMappedIssue(
            'rwc2015', 
            array('custom_section_orders')
        );

        $csOrders = $this->getStub()->callMethod(
            'getCustomSectionOrders',
            $issue
        );

        $this->assertSame(
            '1-1',
            implode('-', array(
                $this->areEqual(
                    $csOrders->length(),
                    $issue->get('custom_section_orders')->length()
                ),
                Registry::get('ArrayHandler')->areEquivalent(
                    $csOrders->toArray(),
                    $issue->get('custom_section_orders')->toArray()
                ),
            ))
        );
    }
    
    protected function getOldEntitiesDir()
    {
        return \str_replace(
            'entities', 
            'old_entities', 
            Registry::get('EntityHandler')->getEntityDataDir('issues')
        );
    }

    protected function moveOldEntities()
    {
        $oldEntitiesDir = $this->getOldEntitiesDir();
        foreach (Registry::get('FileSystemManager')->listdir(
            Registry::get('EntityHandler')->getEntityDataDir('issues')
        ) as $issueDir) {
            Registry::get('FileSystemManager')->move(
                $issueDir,
                $oldEntitiesDir . \BeAmado\OjsMigrator\DIR_SEPARATOR 
                    . \basename($issueDir)
            );
        }
    }

    protected function formExpectedFiles()
    {
        $expectedFiles = array();
        foreach (array(
            $this->getMappedIssue('rwc2015', array('issue_files')),
            $this->getMappedIssue('rwc2011', array('issue_files')),
        ) as $issue) {
            $expectedFiles[] = Registry::get('FileSystemManager')
            ->formPath(array(
                Registry::get('EntityHandler')->getEntityDataDir('issues'),
                $issue->getId(),
                $issue->getId() . '.json'
            ));

            foreach ($issue->get('files')->toArray() as $arrIssueFile) {
                $expectedFiles[] = Registry::get('FileSystemManager')
                ->formPath(array(
                    Registry::get('EntityHandler')->getEntityDataDir('issues'),
                    $issue->getId(),
                    $arrIssueFile['file_name']
                ));
            }
        }

        return $expectedFiles;
    }

    /**
     * @depends testCanImportTheRugbyWorldCup2015Issue
     * @depends testCanImportTheRugbyWorldCup2011Issue
     */
    public function testCanExportTheIssuesFromTheTestJournal()
    {
        $testJournal = Registry::get('JournalsDAO')->read(array(
            'path' => (new JournalMock())->getTestJournal()->getData('path')
        ))->get(0);
        $fsm = Registry::get('FileSystemManager');
        $ih = Registry::get('IssueHandler');

        $ih->setMappedData($testJournal, array(
            'journals' => 'journal_id',
        ));


        $this->moveOldEntities();
        $exported = $ih->exportIssuesFromJournal($testJournal);

        $list = $fsm->listdir($ih->getEntityDataDir('issues'));

        $files = array();
        foreach ($list as $dir) {
            $files = Registry::get('ArrayHandler')->union(
                $files,
                $fsm->listdir($dir)
            );
        }

        $this->assertSame(
            '1-1-2-1',
            implode('-', array(
                (int) $exported,
                $testJournal->getId(),
                count($list),
                (int) Registry::get('ArrayHandler')->equals(
                    $this->formExpectedFiles(),
                    $files
                )
            ))
        );

    }
}
