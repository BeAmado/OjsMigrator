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
    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();
        foreach(array(
            'journals',
            'review_forms',
            'users',
            'sections',
            'section_settings',
            'section_editors',
        ) as $table) {
            Registry::get('DbHandler')->createTableIfNotExists($table);
        }

        Registry::get('EntityHandler')->createOrUpdateInDatabase(
            (new JournalMock())->getTestJournal()
        );

        Registry::get('EntityHandler')->createOrUpdateInDatabase(
            (new ReviewFormMock())->getFirstReviewForm()
        );

        Registry::get('EntityHandler')->createOrUpdateInDatabase(
            (new UserMock())->getUser('hulk')
        );
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
        return (new SectionHandler())->create(
            $this->sectionMock->getSportsSection()
        );
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
}
