<?php

use BeAmado\OjsMigrator\Test\FunctionalTest;
use BeAmado\OjsMigrator\Entity\SubmissionKeywordHandler;
use BeAmado\OjsMigrator\Registry;
use BeAmado\OjsMigrator\Test\FixtureHandler;

// interfaces
use BeAmado\OjsMigrator\Test\StubInterface;

// traits
use BeAmado\OjsMigrator\Test\TestStub;

// mocks
use BeAmado\OjsMigrator\Test\SubmissionMock;

class SubmissionKeywordHandlerTest extends FunctionalTest
{
    public function getStub()
    {
        return new class extends SubmissionKeywordHandler {
            use TestStub;
        };
    }

    protected function handler()
    {
        return Registry::get('SubmissionHandler');
    }

    protected function createRWC2015()
    {
        return (new SubmissionMock())->getSubmission('rwc2015');
    }

    public static function setUpBeforeClass($args = [
        'createTables' => [
            'submissions',
            'submission_search_objects',
            'submission_search_object_keywords',
            'submission_search_keyword_list',
        ],
    ]) : void {
        parent::setUpBeforeClass($args);
        $created = (new FixtureHandler())->createSingle(
            'submission',
            'rwc2015',
            false,
            ['files']
        );
        (new FixtureHandler())->createKeywords('rwc2015');
    }

//    public function testCanRegisterTheRwc2015Submission()
//    {
//        $submission = $this->createRWC2015();
//        $registered = $this->getStub()->createOrUpdateInDatabase($submission);
//
//        $fromDb = $this->handler()->getDAO()->read([
//            $this->handler()
//                 ->formIdField() => Registry::get('DataMapper')->getMapping(
//                 
//                $this->handler()->formTableName(),
//                $submission->getId()
//            )
//        ]);
//
//        $this->assertSame(
//            '1-1',
//            implode('-', [
//                (int) $registered,
//                $fromDb->length(),
//            ])
//        );
//    }

    public function testCanImportAKeyword()
    {
        $submission = $this->createRWC2015();

        $keyword = $submission->get('keywords')->get(0);

        $imported = $this->getStub()->callMethod(
            'importSubmissionSearchObject',
            $keyword
        );

        $objectId = Registry::get('DataMapper')->getMapping(
            $this->handler()->formTableName('search_objects'),
            $keyword->get('object_id')->getValue()
        );

        $searchObjects = $this->handler()->getDAO('search_objects')->read([
            'object_id' => $objectId,
        ]);

        $searchObjectKeywords = $this->handler()->getDAO(
            'search_object_keywords'
        )->read([
            'object_id' => $objectId,
        ]);

        $keywordList = $this->handler()->getDAO('search_keyword_list')->read([
            'keyword_id' => $searchObjectKeywords->get(0)
                                                 ->getData('keyword_id')
        ]);

        $this->assertSame(
            '1-1-1-1-1-best',
            implode('-', [
                (int) $imported,
                (int) is_numeric($objectId),
                $searchObjects->length(),
                $searchObjectKeywords->length(),
                $keywordList->length(),
                $keywordList->get(0)->getData('keyword_text'),
            ])
        );
    }

    public function testCanImportTheKeywordsOfTheRwc2015Submission()
    {
        $submission = $this->createRWC2015();
        $submissionId = Registry::get('DataMapper')->getMapping(
            $this->handler()->formTableName(),
            $this->handler()->getSubmissionId($submission)
        );

        $searchObjectsBefore = $this->handler()->getDAO('search_objects')->read([
            $this->handler()->formIdField() => $submissionId,
        ]);

        $imported = Registry::get('SubmissionKeywordHandler')->importKeywords(
            $submission
        );

        $searchObjects = $this->handler()->getDAO('search_objects')->read([
            $this->handler()->formIdField() => $submissionId,
        ]);

        $this->assertSame(
            '1-1-3',
            implode('-', [
                (int) $searchObjectsBefore->length(),
                (int) $imported,
                $searchObjects->length(),
            ])
        );
    }

    /**
     * @depends testCanImportTheKeywordsOfTheRwc2015Submission
     */
    public function testCanExportTheKeywordsOfTheRwc2015Submission()
    {
        $submission = $this->createRWC2015();
        $submissionId = Registry::get('DataMapper')->getMapping(
            $this->handler()->formTableName(),
            $this->handler()->getSubmissionId($submission)
        );

        $this->getStub()->exportSearchObjects($submissionId);
        $fsm = Registry::get('FileSystemManager');

        $this->assertFalse(true);
    }
}
