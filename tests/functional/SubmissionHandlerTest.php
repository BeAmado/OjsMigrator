<?php

use BeAmado\OjsMigrator\Test\FunctionalTest;
use BeAmado\OjsMigrator\Registry;
use BeAmado\OjsMigrator\Entity\SubmissionHandler;
use BeAmado\OjsMigrator\Entity\SubmissionFileHandler;
use BeAmado\OjsMigrator\Test\FixtureHandler;

// interfaces 
use BeAmado\OjsMigrator\Test\StubInterface;

// traits
use BeAmado\OjsMigrator\Test\TestStub;

// mocks
use BeAmado\OjsMigrator\Test\SubmissionMock;

class SubmissionHandlerTest extends FunctionalTest implements StubInterface
{
    public static function formContent($file)
    {
        return 'This is the file with name "' 
            . $file->get('file_name')->getValue() . '"';
    }

    protected static function formPathInEntitiesDir($file)
    {
        return Registry::get('SubmissionFileHandler')
                       ->formFilePathInEntitiesDir(
            $file->get('file_name')->getValue()
        );
    }

    protected static function createTheSubmissionFiles()
    {
        foreach ([
            'rugby-worldcup-2015',
        ] as $name) {
            $sm = (new SubmissionMock())->getSubmission($name);
            if ($sm->hasAttribute('files'))
                $sm->get('files')->forEachValue(function($file){
                    Registry::get('FileHandler')->write(
                        self::formPathInEntitiesDir($file),
                        self::formContent($file)
                    );
                });
        }
    }

    public static function setUpBeforeClass($args = [
        'createTables' => [
            'submissions',
            'submission_settings',
            'submission_files',
            'published_submissions',
        ],
    ]) : void {
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

        self::createTheSubmissionFiles();
    }

    public function getStub()
    {
        return new class extends SubmissionHandler {
            use TestStub;
        };
    }

    protected function smfHrStub()
    {
        return new class extends SubmissionFileHandler {
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

    protected function mapFileName($file)
    {
        return $this->smfHrStub()->callMethod(
            'getMappedFileName',
            $file->get('file_name')->getValue()
        );
    }

    public function testCanImportTheSubmissionFiles()
    {
        $submission = $this->createRWC2015();

        $imported = $this->getStub()->callMethod(
            'importSubmissionFiles',
            $submission
        );

        $journalId = Registry::get('DataMapper')->getMapping(
            'journals',
            $submission->getData('journal_id')
        );

        $file1 = $submission->get('files')->get(0);
        $file2 = $submission->get('files')->get(1);

        $this->assertSame(
            implode(';', [
                1,
                self::formContent($file1),
                self::formContent($file2),
            ]),
            implode(';', [
                (int) $imported,
                Registry::get('FileHandler')->read(
                    Registry::get('SubmissionFileHandler')->formPathByFileName(
                        $this->mapFileName($file1),
                        $journalId
                    )
                ),
                Registry::get('FileHandler')->read(
                    Registry::get('SubmissionFileHandler')->formPathByFileName(
                        $this->mapFileName($file2),
                        $journalId
                    )
                ),
            ])
        );
    }

    public function testCanImportThePublishedSubmissionData()
    {
        $submission = $this->createRWC2015();
        $pub = $submission->get('published');
        $idField = $this->handler()->formIdField('published');

        $imported = $this->getStub()->callMethod(
            'importPublished',
            $pub
        );

        $mappedId = Registry::get('DataMapper')->getMapping(
            $this->handler()->formTableName('published'),
            $pub->get($idField)->getValue()
        );
        $fromDb = $this->handler()->getDAO('published')->read([
             $idField => $mappedId,
        ]);
        $this->handler()->setMappedData($pub, [
            'issues' => 'issue_id',
            $this->handler()->formTableName('published') => $idField,
            $this->handler()->formTableName() => 
                $this->handler()->formIdField(),
        ]);

        $this->assertSame(
            '1-1-1-1',
            implode('-', [
                (int) $imported,
                $fromDb->length(),
                (int) is_numeric($mappedId),
                (int) $this->handler()->areEqual(
                    $fromDb->get(0),
                    $pub
                ),
            ])
        );
    }

}
