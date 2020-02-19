<?php

use BeAmado\OjsMigrator\FunctionalTest;
use BeAmado\OjsMigrator\Registry;
use BeAmado\OjsMigrator\Entity\SectionHandler;

// interfaces
use BeAmado\OjsMigrator\StubInterface;

// traits
use BeAmado\OjsMigrator\TestStub;

// mocks
use BeAmado\OjsMigrator\SectionMock;
use BeAmado\OjsMigrator\JournalMock;
use BeAmado\OjsMigrator\ReviewFormMock;
use BeAmado\OjsMigrator\UserMock;

class SectionHandlerTest extends FunctionalTest implements StubInterface
{
    public static function setUpBeforeClass($args = array(
        'createTables' => array(
            'journals',
            'review_forms',
            'users',
            'sections',
            'section_settings',
            'section_editors',
        ),
    )) : void {
        parent::setUpBeforeClass($args);

        Registry::get('EntityHandler')->createOrUpdateInDatabase(
            (new JournalMock())->getTestJournal()
        );

        foreach (array(
            'first', 
            'second'
        ) as $revForm) {
            Registry::get('EntityHandler')->createOrUpdateInDatabase(
                (new ReviewFormMock())->getReviewForm($revForm)
            );
        }

        foreach (array(
            'hulk',
            'batman',
            'ironman'
        ) as $username) {
            Registry::get('EntityHandler')->createOrUpdateInDatabase(
                (new UserMock())->getUser($username)
            );
        }
    }

    public function getStub()
    {
        return new class extends SectionHandler {
            use TestStub;
        };
    }

    public function __construct()
    {
        parent::__construct();
        $this->sectionMock = new SectionMock();
    }

    protected function createSportsSection()
    {
        return $this->sectionMock->getSportsSection();
    }

    protected function createSciencesSection()
    {
        return $this->sectionMock->getSciencesSection();
    }

    public function testCanCreateTheSportsSection()
    {
        $section = $this->createSportsSection();
        $testJournal = (new JournalMock())->getTestJournal();
        $firstRF = (new ReviewFormMock())->getFirstReviewForm();
        $hulk = (new UserMock())->getUser('hulk');

        $this->assertSame(
            '1-1-1-1',
            implode('-', array(
                (int) $this->areEqual(
                    $section->get('journal_id')->getValue(),
                    $testJournal->get('journal_id')->getValue()
                ),
                (int) $this->areEqual(
                    $section->get('review_form_id')->getValue(),
                    $firstRF->get('review_form_id')->getValue()
                ),
                (int) $this->areEqual(
                    $section->get('editors')->get(0)
                            ->get('user_id')->getValue(),
                    $hulk->get('user_id')->getValue()
                ),
                (int) $this->areEqual(
                    $section->get('editors')->get(0)
                            ->get('journal_id')->getValue(),
                    $testJournal->get('journal_id')->getValue()
                ),
            ))
        );
    }

    public function testCanCreateTheSciencesSection()
    {
        $section = $this->createSciencesSection();
        $testJournal = (new JournalMock())->getTestJournal();
        $secondRF = (new ReviewFormMock())->getSecondReviewForm();
        $ironman = (new UserMock())->getUser('ironman');
        $batman = (new UserMock())->getUser('batman');
        $hulk = (new UserMock())->getUser('hulk');

        $this->assertSame(
            '2-3-1-1-1-1-1',
            implode('-', array(
                $section->get('settings')->length(),
                $section->get('editors')->length(),
                (int) $this->areEqual(
                    $section->getData('journal_id'),
                    $testJournal->getId()
                ),
                (int) $this->areEqual(
                    $section->getData('review_form_id'),
                    $secondRF->getId()
                ),
                (int) $this->areEqual(
                    $section->get('editors')->get(0)
                            ->get('user_id')->getValue(),
                    $ironman->getId()
                ),
                (int) $this->areEqual(
                    $section->get('editors')->get(1)
                            ->get('user_id')->getValue(),
                    $batman->getId()
                ),
                (int) $this->areEqual(
                    $section->get('editors')->get(2)
                            ->get('user_id')->getValue(),
                    $hulk->getId()
                ),
            ))
        );

    }

    public function testCanRegisterTheSportsSection()
    {
        $sportsSection = $this->createSportsSection();
        $registered = $this->getStub()->callMethod(
            'registerSection',
            $sportsSection
        );

        $fromDb = Registry::get('SectionsDAO')->read(array(
            'section_id' => Registry::get('DataMapper')->getMapping(
                'sections',
                $sportsSection->getId()
            )
        ));
        
        $this->assertSame(
            '1-1-1-1',
            implode('-', array(
                (int) $registered,
                (int) $fromDb->length(),
                (int) $this->areEqual(
                    Registry::get('DataMapper')->getMapping(
                        'journals',
                        $sportsSection->getData('journal_id')
                    ),
                    $fromDb->get(0)->getData('journal_id')
                ),
                (int) $this->areEqual(
                    Registry::get('DataMapper')->getMapping(
                        'review_forms',
                        $sportsSection->getData('review_form_id')
                    ),
                    $fromDb->get(0)->getData('review_form_id')
                )
            ))
        );
    }

    /**
     * @depends testCanRegisterTheSportsSection
     */
    public function testCanImportTheSportsSectionFirstSetting()
    {
        $sectionSetting = $this->createSportsSection()->get('settings')->get(0);

        $imported = $this->getStub()->callMethod(
            'importSectionSetting',
            $sectionSetting
        );

        $fromDb = Registry::get('SectionSettingsDAO')->read(array(
            'section_id' => Registry::get('DataMapper')->getMapping(
                'sections',
                $sectionSetting->get('section_id')->getValue()
            )
        ));

        $this->assertSame(
            '1-1-1',
            implode('-', array(
                (int) $imported,
                (int) $fromDb->length(),
                (int) Registry::get('EntityHandler')->areEqual(
                    $sectionSetting,
                    $fromDb->get(0),
                    array('section_id')
                )
            ))
        );
    }

    /**
     * @depends testCanRegisterTheSportsSection
     */
    public function testCanImportTheSportsSectionFirstEditor()
    {
        $sectionEditor = $this->createSportsSection()->get('editors')->get(0);

        $imported = $this->getStub()->callMethod(
            'importSectionEditor',
            $sectionEditor
        );

        $fromDb = Registry::get('SectionEditorsDAO')->read(array(
            'section_id' => Registry::get('DataMapper')->getMapping(
                'sections',
                $sectionEditor->get('section_id')->getValue()
            )
        ));

        $this->assertSame(
            '1-1-1-1-1-1',
            implode('-', array(
                (int) $imported,
                (int) $fromDb->length(),
                (int) Registry::get('EntityHandler')->areEqual(
                    $fromDb->get(0),
                    $sectionEditor,
                    array('section_id', 'journal_id', 'user_id')
                ),
                (int) $this->areEqual(
                    $fromDb->get(0)->getData('section_id'),
                    Registry::get('DataMapper')->getMapping(
                        'sections',
                        $sectionEditor->get('section_id')->getValue()
                    )
                ),
                (int) $this->areEqual(
                    $fromDb->get(0)->getData('user_id'),
                    Registry::get('DataMapper')->getMapping(
                        'users',
                        $sectionEditor->get('user_id')->getValue()
                    )
                ),
                (int) $this->areEqual(
                    $fromDb->get(0)->getData('journal_id'),
                    Registry::get('DataMapper')->getMapping(
                        'journals',
                        $sectionEditor->get('journal_id')->getValue()
                    )
                ),
            ))
        );
    }

    public function testCanImportTheSportsSection()
    {
        $section = $this->createSportsSection();

        $imported = Registry::get('SectionHandler')->importSection($section);

        Registry::get('EntityHandler')->setMappedData($section, array(
            'sections' => 'section_id',
            'journals' => 'journal_id',
            'review_forms' => 'review_form_id',
        ));

        $section->get('settings')->forEachValue(function($setting) {
            Registry::get('EntityHandler')->setMappedData($setting, array(
                'sections' => 'section_id',
            ));
        });

        $section->get('editors')->forEachValue(function($editor) {
            Registry::get('EntityHandler')->setMappedData($editor, array(
                'sections' => 'section_id',
                'journals' => 'journal_id',
                'users' => 'user_id',
            ));
        });

        $sectionsFromDb = Registry::get('SectionsDAO')->read($section);

        $settingsFromDb = Registry::get('SectionSettingsDAO')->read(array(
            'section_id' => $section->getId()
        ));

        $editorsFromDb = Registry::get('SectionEditorsDAO')->read(array(
            'section_id' => $section->getId()
        ));

        $this->assertSame(
            '1-1-2-2-1-1-1',
            implode('-', array(
                (int) $imported,
                $sectionsFromDb->length(),
                $settingsFromDb->length(),
                $editorsFromDb->length(),
                (int) Registry::get('EntityHandler')->areEqual(
                    $section,
                    $sectionsFromDb->get(0)
                ),
                (int) Registry::get('ArrayHandler')->areEquivalent(
                    $settingsFromDb->toArray(),
                    $section->get('settings')->toArray()
                ),
                (int) Registry::get('ArrayHandler')->areEquivalent(
                    $editorsFromDb->toArray(),
                    $section->get('editors')->toArray()
                )
            ))
        );
    }

    /**
     * @depends testCanImportTheSportsSection
     */
    public function testCanImportTheSciencesSection()
    {
        $section = $this->createSciencesSection();

        $imported = Registry::get('SectionHandler')->importSection($section);

        Registry::get('EntityHandler')->setMappedData($section, array(
            'sections' => 'section_id',
            'review_forms' => 'review_form_id',
            'journals' => 'journal_id',
        ));

        $section->get('settings')->forEachValue(function($setting) {
            Registry::get('EntityHandler')->setMappedData($setting, array(
                'sections' => 'section_id'
            ));
        });

        $section->get('editors')->forEachValue(function($editor) {
            Registry::get('EntityHandler')->setMappedData($editor, array(
                'sections' => 'section_id',
                'users' => 'user_id',
                'journals' => 'journal_id',
            ));
        });

        $sectionsFromDb = Registry::get('SectionsDAO')->read($section);
        $settingsFromDb = Registry::get('SectionSettingsDAO')->read(array(
            'section_id' => $section->getId()
        ));
        $editorsFromDb = Registry::get('SectionEditorsDAO')->read(array(
            'section_id' => $section->getId()
        ));

        $this->assertSame(
            '1-1-1-1-1',
            implode('-', array(
                (int) $imported,
                $sectionsFromDb->length(),
                (int) Registry::get('EntityHandler')->areEqual(
                    $sectionsFromDb->get(0),
                    $section
                ),
                (int) Registry::get('ArrayHandler')->areEquivalent(
                    $settingsFromDb->toArray(),
                    $section->get('settings')->toArray()
                ),
                (int) Registry::get('ArrayHandler')->areEquivalent(
                    $editorsFromDb->toArray(),
                    $section->get('editors')->toArray()
                ),
            ))
        );
    }

    protected function getMappedSection($name)
    {
        $section = null;
        switch(strtolower($name)) {
            case 'sports':
                $section = $this->createSportsSection();
                break;
            case 'sciences':
                $section = $this->createSciencesSection();
                break;
        }

        Registry::get('EntityHandler')->setMappedData($section, array(
            'sections' => 'section_id',
            'journals' => 'journal_id',
            'review_forms' => 'review_form_id',
        ));

        $section->get('settings')->forEachValue(function($setting) {
            Registry::get('EntityHandler')->setMappedData($setting, array(
                'sections' => 'section_id',
            ));
        });

        $section->get('editors')->forEachValue(function($editor) {
            Registry::get('EntityHandler')->setMappedData($editor, array(
                'sections' => 'section_id',
                'users' => 'user_id',
                'journals' => 'journal_id',
            ));
        });

        return $section;
    }

    /**
     * @depends testCanImportTheSportsSection
     */
    public function testCanGetTheSectionSettings()
    {
        $sportsSection = $this->getMappedSection('sports');

        $settings = $this->getStub()->callMethod(
            'getSectionSettings',
            $sportsSection
        );

        $this->assertSame(
            '2-1',
            implode('-', array(
                $settings->length(),
                (int) Registry::get('ArrayHandler')->areEquivalent(
                    $sportsSection->get('settings')->toArray(),
                    $settings->toArray()
                )
            ))
        );
    }

    /**
     * @depends testCanImportTheSciencesSection
     */
    public function testCanGetTheSectionEditors()
    {
        $sciencesSection = $this->getMappedSection('sciences');

        $editors = $this->getStub()->callMethod(
            'getSectionEditors',
            $sciencesSection
        );

        $this->assertSame(
            '3-1',
            implode('-', array(
                $editors->length(),
                (int) Registry::get('ArrayHandler')->areEquivalent(
                    $sciencesSection->get('editors')->toArray(),
                    $editors->toArray()
                )
            ))
        );
    }

    /**
     * @depends testCanImportTheSportsSection
     * @depends testCanImportTheSciencesSection
     * @depends testCanGetTheSectionSettings
     * @depends testCanGetTheSectionEditors
     */
    public function testCanExportTheSectionsFromTheTestJournal()
    {
        $journal = Registry::get('JournalsDAO')->read(array(
            'path' => 'test_journal',
        ))->get(0);

        Registry::get('SectionHandler')->exportSectionsFromJournal($journal);
        $eh = Registry::get('EntityHandler');
        $ah = Registry::get('ArrayHandler');

        $sectionsList = Registry::get('FileSystemManager')->listdir(
            $eh->getEntityDataDir('sections')
        );

        sort($sectionsList);

        $exportedSportsSection = Registry::get('JsonHandler')->createFromFile(
            $sectionsList[0]
        );
        $exportedSciencesSection = Registry::get('JsonHandler')->createFromFile(
            $sectionsList[1]
        );

        $mockedSportsSection = $this->getMappedSection('sports');
        $mockedSciencesSection = $this->getMappedSection('sciences');

        $this->assertSame(
            '2-1-1-1-1-1-1',
            implode('-', array(
                count($sectionsList),
                (int) $eh->areEqual(
                    $mockedSportsSection,
                    $exportedSportsSection
                ),
                (int) $eh->areEqual(
                    $mockedSciencesSection,
                    $exportedSciencesSection
                ),
                (int) $ah->areEquivalent(
                    $mockedSportsSection->get('settings')->toArray(),
                    $exportedSportsSection->get('settings')->toArray()
                ),
                (int) $ah->areEquivalent(
                    $mockedSportsSection->get('editors')->toArray(),
                    $exportedSportsSection->get('editors')->toArray()
                ),
                (int) $ah->areEquivalent(
                    $mockedSciencesSection->get('settings')->toArray(),
                    $exportedSciencesSection->get('settings')->toArray()
                ),
                (int) $ah->areEquivalent(
                    $mockedSciencesSection->get('editors')->toArray(),
                    $exportedSciencesSection->get('editors')->toArray()
                ),
            ))
        );
    }
}
