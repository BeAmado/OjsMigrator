<?php

use BeAmado\OjsMigrator\FunctionalTest;
use BeAmado\OjsMigrator\Registry;
use BeAmado\OjsMigrator\Entity\SubmissionHandler;

use BeAmado\OjsMigrator\FixtureHandler;

// interfaces 
use BeAmado\OjsMigrator\TestStub;

// traits
use BeAmado\OjsMigrator\StubInterface;

// mocks
use BeAmado\OjsMigrator\SubmissionMock;
use BeAmado\OjsMigrator\JournalMock;
use BeAmado\OjsMigrator\SectionMock;
use BeAmado\OjsMigrator\IssueMock;
use BeAmado\OjsMigrator\UserMock;

class SubmissionHandlerTest extends FunctionalTest implements StubInterface
{
    public static function setUpBeforeClass($args = []) : void
    {
        parent::setUpBeforeClass($args);
        (new FixtureHandler())->createSeveral([
            'journals' => [
                'test_journal',
            ],
            'users' => [
                'ironman'
            ],
            'sections' => [
                'sports',
                'sciences',
            ],
            'issues' => [
                '2011',
                '2015',
            ],
        ]);
    }

    public function getStub()
    {
        return new class extends SubmissionHandler {
            use TestStub;
        };
    }

    public function testTheSubmissionAliasIsArticle()
    {
        $this->assertSame(
            'article',
            $this->getStub()->getEntityAlias()
        );
    }

    public function testCanFormTheTableNameForTheArticleFiles()
    {
        $this->assertSame(
            'article_files',
            Registry::get('SubmissionHandler')->formTableName('files')
        );
    }

    protected function createRWC2015()
    {
        return (new SubmissionMock())->getRWC2015();
    }

    public function testCanCreateTheRwc2015Submission()
    {
        $submission = $this->createRWC2015();
        $this->assertSame(
            '1',
            implode('-', array(
                (int) Registry::get('EntityHandler')->isEntity($submission),
            ))
        );
    }
}
