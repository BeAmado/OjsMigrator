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

    protected function getKeywordsAndPositions($searchObjectsArr)
    {
        $data = [
            'keywords' => [],
            'positions' => [],
        ];
        foreach ($searchObjectsArr as $objArr) {
            foreach ($objArr['search_object_keywords'] as $sok) {
                $data['positions'][] = $sok['pos'];
                $data['keywords'][] = $sok['keyword_list']['keyword_text'];
            }
        }

        return $data;
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
        $searchObjectsDir = $fsm->formPath(array(
            $this->handler()->formSubmissionEntityDataDir($submissionId),
            'search_objects',
        ));

        $mocked = $this->getKeywordsAndPositions(
            $submission->get('keywords')->toArray()
        );

        $exported = $this->getKeywordsAndPositions(array_map(
            function($filename) {
                return Registry::get('JsonHandler')->createFromFile($filename)
                                                   ->toArray();
            },
            $fsm->listdir($searchObjectsDir)
        ));

        $this->assertSame(
            implode('-', [
                1,
                $submission->get('keywords')->length(),
                1,
                1,
            ]),
            implode('-', [
                (int) $fsm->dirExists($searchObjectsDir),
                (int) count($fsm->listdir($searchObjectsDir)),
                (int) Registry::get('ArrayHandler')->equals(
                    $mocked['keywords'],
                    $exported['keywords']
                ),
                (int) Registry::get('ArrayHandler')->equals(
                    $mocked['positions'],
                    $exported['positions']
                ),
            ])
        );
    }

    public function testMapsTheKeywordIdWhenNotMappedAndTextExists()
    {
        $keywordList = Registry::get('EntityHandler')->create(
            $this->handler()->formTableName('search_keyword_list'),
            [
                'keyword_id' => 272,
                'keyword_text' => 'winger',
            ]
        );
        $dao = $this->handler()->getDAO('search_keyword_list');
        $keywordsBefore = $dao->read();

        $imported = $this->getStub()->callMethod(
            'importKeywordList',
            $keywordList
        );

        $keywordsAfter = $dao->read();

        $this->assertSame(
            '1-1',
            implode('-', [
                (int) $imported,
                (int) Registry::get('ArrayHandler')->areEquivalent(
                    $keywordsBefore->toArray(),
                    $keywordsAfter->toArray()
                ),
            ])
        );

    }
}
