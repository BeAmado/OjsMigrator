<?php

use BeAmado\OjsMigrator\Test\FunctionalTest;
use BeAmado\OjsMigrator\Registry;
use BeAmado\OjsMigrator\Entity\SubmissionHandler;
use BeAmado\OjsMigrator\Test\FixtureHandler;

// interfaces 
use BeAmado\OjsMigrator\Test\StubInterface;

// traits
use BeAmado\OjsMigrator\Test\TestStub;

// mocks
use BeAmado\OjsMigrator\Test\SubmissionMock;

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
            'submissions' => [],
        ]);
    }

    public function getStub()
    {
        return new class extends SubmissionHandler {
            use TestStub;
        };
    }

    protected function handler()
    {
        return Registry::get('SubmissionHandler');
    }
    
    protected function dataMapper()
    {
        return Registry::get('DataMapper');
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
            implode('-', [
                1,
                Registry::get('SubmissionHandler')->formTableName(),
            ]),
            implode('-', [
                (int) Registry::get('EntityHandler')->isEntity($submission),
                $submission->getTableName(),
            ])
        );
    }

    /**
     * @depends testCanCreateTheRwc2015Submission
     */
    public function testCanRegisterTheRugbyWorldCup2015Submission()
    {
        $submission = $this->createRWC2015();
            
        $registered = $this->getStub()->callMethod(
            'registerSubmission',
            $submission
        );

        $fromDb = $this->handler()->getDAO()->read([
            $this->handler()->formIdField() => $this->dataMapper()->getMapping(
                $this->handler()->formTableName(),
                $submission->getId()
            ),
        ]);

        $this->handler()->setMappedData($submission, [
            'sections' => 'section_id',
            'users' => 'user_id',
            'journals' => 'journal_id',
        ]);

        $this->assertSame(
            '1-1-1',
            implode('-', [
                (int) $registered,
                $fromDb->length(),
                (int) $this->handler()->areEqual(
                    $fromDb->get(0),
                    $submission
                ),
            ])
        );
    }

    public function testCanImportASubmissionSetting()
    {
//        $submission = $this->createRWC2015();
        $setting = $this->createRWC2015()->get('settings')->get(0);

        $imported = $this->getStub()->callMethod(
            'importSubmissionSetting',
            $setting
        );

        $this->handler()->setMappedData($setting, [
            $this->handler()->formTableName() => $this->handler()
                                                      ->formIdField(),
        ]);

        $fromDb = $this->handler()->getDAO('settings')->read([
            'locale' => $setting->get('locale')->getValue(),
            'setting_name' => $setting->get('setting_name')->getValue(),
            'setting_value' => $setting->get('setting_value')->getValue(),
        ]);

        $this->assertSame(
            '1-1-1',
            implode('-', [
                (int) $imported,
                $fromDb->length(),
                (int) $this->handler()->areEqual(
                    $fromDb->get(0),
                    $setting
                )
            ])
        );
    }

}
