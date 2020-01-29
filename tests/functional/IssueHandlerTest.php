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

        Registry::get('EntityHandler')->createOrUpdateInDatabase(
            (new JournalMock())->getTestJournal()
        );

        foreach(array(
            'sciences',
            'sports',
        ) as $section) {
            Registry::get('EntityHandler')->createOrUpdateInDatabase(
                (new SectionMock())->getSection($section)
            );
        }

        Registry::get('FileSystemManager')->createDir(
            (new OjsScenarioTester())->getOjsFilesDir()
        );
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

}
