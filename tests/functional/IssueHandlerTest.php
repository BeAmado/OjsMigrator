<?php

use BeAmado\OjsMigrator\FunctionalTest;
use BeAmado\OjsMigrator\Entity\IssueHandler;
use BeAmado\OjsMigrator\Registry;
use BeAmado\OjsMigrator\OjsScenarioTester;

// interfaces
use BeAmado\OjsMigrator\StubInterface;

// traits 
use BeAmado\OjsMigrator\TestStub;

// mocks
use BeAmado\OjsMigrator\JournalMock;
use BeAmado\OjsMigrator\IssueMock;
use BeAmado\OjsMigrator\SectionMock; // for the custom_section_orders data

class IssueHandlerTest extends FunctionalTest implements StubInterface
{
    protected static function createTheIssueFiles($issue)
    {
        $issue->get('files')->forEachValue(function($issueFile) {
            Registry::get('FileHandler')->write(
                Registry::get('FileSystemManager')->formPath(array(
                    Registry::get('IssueHandler')->getEntityDataDir('issues'),
                    $issueFile->get('issue_id')->getValue(),
                    $issueFile->get('file_name')->getValue(),
                )),
                'This is the issue with original file name '
                    . $issueFile->get('file_name')->getValue()
            );
        });
    }

    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();

        foreach(array(
            'journals',
            'sections',
            'issues',
            'issue_settings',
            'issue_galleys',
            'issue_galley_settings',
            'issue_files',
            'custom_issue_orders',
            'custom_section_orders',
        ) as $table) {
            Registry::get('DbHandler')->createTableIfNotExists($table);
        }

        $testJournal = (new JournalMock())->getTestJournal();
        Registry::get('EntityHandler')->createOrUpdateInDatabase($testJournal);

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
                $testJournal->getId()
            )
        ));

        foreach(array(
            (new IssueMock())->getRWC2015Issue(),
        ) as $issue) {
            $fsm->createDir($fsm->formPath(array(
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
        $this->scenario = new OjsScenarioTester();
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

    public function testCanCreateTheRugbyWorldCup2015Issue()
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

        $this->assertSame(
            '1',
            implode('-', array(
                (int) $imported,
            ))
        );
    }
}
