<?php

use BeAmado\OjsMigrator\Test\FunctionalTest;
use BeAmado\OjsMigrator\Entity\SubmissionKeywordHandler;
use BeAmado\OjsMigrator\Registry;

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
    }

    public function testCanRegisterTheRwc2015Submission()
    {
        $submission = $this->createRWC2015();
        $registered = $this->getStub()->createOrUpdateInDatabase($submission);

        $fromDb = $this->handler()->getDAO()->read([
            $this->handler()
                 ->formIdField() => Registry::get('DataMapper')->getMapping(
                 
                $this->handler()->formTableName(),
                $submission->getId()
            )
        ]);

        $this->assertSame(
            '1-1',
            implode('-', [
                (int) $registered,
                $fromDb->length(),
            ])
        );
    }

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
}