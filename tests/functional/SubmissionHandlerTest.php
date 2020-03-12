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
            'submission_supplementary_files',
            'submission_supp_file_settings',
            'submission_galleys',
            'submission_galley_settings',
            'submission_comments',
            'authors',
            'author_settings',
            'edit_assignments',
            'edit_decisions',
            'review_rounds',
            'review_assignments',
            'submission_search_objects',
            'submission_search_object_keywords',
            'submission_search_keyword_list',
        ],
    ]) : void {
        parent::setUpBeforeClass($args);
        (new FixtureHandler())->createSeveral([
            'journals' => [
                'test_journal',
            ],
            'users' => [
                'ironman',
                'hulk',
                'greenlantern',
                'thor',
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

    public function testCanImportASubmissionSupplementaryFileData()
    {
        $submission = $this->createRWC2015();
        $suppFile = $submission->get('supplementary_files')->get(0);

        $imported = $this->getStub()->callMethod(
            'importSubmissionSuppFile',
            $suppFile
        );

        $suppId = Registry::get('DataMapper')->getMapping(
            $this->handler()->formTableName('supplementary_files'),
            $suppFile->get('supp_id')->getValue()
        );

        $fromDb = $this->handler()->getDAO('supplementary_files')->read([
            'supp_id' => $suppId,
        ]);

        $settings = $this->handler()->getDAO('supp_file_settings')->read([
            'supp_id' => $suppId,
        ]);

        $this->assertSame(
            '1-1-1-1-2-1',
            implode('-', [
                (int) $suppFile->hasAttribute($this->handler()->formIdField()),
                (int) $imported,
                (int) is_numeric($suppId),
                $fromDb->length(),
                $settings->length(),
                $this->areEqual(
                    Registry::get('DataMapper')->getMapping(
                        $this->handler()->formTableName(),
                        $submission->getId()
                    ),
                    $fromDb->get(0)->getData($this->handler()->formIdField())
                ),
            ])
        );
    }

    public function testCanImportASubmissionGalley()
    {
        $galley = $this->createRWC2015()->get('galleys')->get(0);

        $imported = $this->getStub()->callMethod(
            'importSubmissionGalley',
            $galley
        );

        $galleyId = Registry::get('DataMapper')->getMapping(
            $this->handler()->formTableName('galleys'),
            $galley->get('galley_id')->getValue()
        );

        $galleys = $this->handler()->getDAO('galleys')->read([
            'galley_id' => $galleyId,
        ]);

        $settings = $this->handler()->getDAO('galley_settings')->read([
            'galley_id' => $galleyId,
        ]);

        $this->assertSame(
            '1-1-1-1-1',
            implode('-', [
                (int) $galley->hasAttribute($this->handler()->formIdField()),
                (int) $imported,
                (int) is_numeric($galleyId),
                $galleys->length(),
                $settings->length(),
            ])
        );
    }

    public function testCanImportASubmissionComment()
    {
        $submission = $this->CreateRWC2015();
        $comment = $submission->get('comments')->get(0);

        $imported = $this->getStub()->callMethod(
            'importSubmissionComment',
            $comment
        );

        $commentId = Registry::get('DataMapper')->getMapping(
            $this->handler()->formTableName('comments'),
            $comment->get('comment_id')->getValue()
        );

        $commentFromDb = $this->handler()->getDAO('comments')->read([
            'comment_id' => $commentId,
        ]);

        $this->assertSame(
            '1-1-1-1',
            implode('-', [
                (int) $imported,
                $commentFromDb->length(),
                (int) $this->areEqual(
                    Registry::get('DataMapper')->getMapping(
                        $this->handler()->formTableName(),
                        $submission->getId()
                    ),
                    $commentFromDb->get(0)
                                  ->getData($this->handler()->formIdField())
                ),
                (int) $this->handler()->areEqual(
                    $commentFromDb->get(0),
                    $comment,
                    ['author_id', $this->handler()->formIdField()]
                )
            ])
        );
    }

    public function testCanImportAnAuthor()
    {
        $submission = $this->createRWC2015();
        $author = $submission->get('authors')->get(0);

        $imported = $this->getStub()->callMethod(
            'importAuthor',
            $author
        );

        $authorId = Registry::get('DataMapper')->getMapping(
            'authors',
            $author->get('author_id')->getValue()
        );

        $authorsFromDb = Registry::get('AuthorsDAO')->read([
            'author_id' => $authorId,
        ]);

        $author->get('settings')->forEachValue(function($setting) {
            $this->handler()->setMappedData(
                $setting,
                ['authors' => 'author_id',]
            );
        });

        $settingsFromDb = Registry::get('AuthorSettingsDAO')->read([
            'author_id' => $authorId,
        ]);

        $this->assertSame(
            '1-1-1-1-1-2-1',
            implode('-', [
                (int) $imported,
                (int) is_numeric($authorId),
                $authorsFromDb->length(),
                (int) $this->handler()->areEqual(
                    $authorsFromDb->get(0),
                    $author,
                    ['submission_id']
                ),
                (int) $this->areEqual(
                    $authorsFromDb->get(0)->getData('submission_id'),
                    Registry::get('DataMapper')->getMapping(
                        $this->handler()->formTableName(),
                        $submission->getId()
                    )
                ),
                $settingsFromDb->length(),
                (int) Registry::get('ArrayHandler')->areEquivalent(
                    $settingsFromDb->toArray(),
                    $author->get('settings')->toArray()
                ),
            ])
        );
    }

    public function testCanImportAnEditAssignment()
    {
        $submission = $this->createRWC2015();
        $assign = $submission->get('edit_assignments')->get(0);

        $imported = $this->getStub()->callMethod(
            'importEditAssignment',
            $assign
        );

        $editId = Registry::get('DataMapper')->getMapping(
            'edit_assignments',
            $assign->get('edit_id')->getValue()
        );

        $fromDb = Registry::get('EditAssignmentsDAO')->read([
            'edit_id' => $editId,
        ]);

        $this->assertSame(
            '1-1-1',
            implode('-', [
                (int) $imported,
                $fromDb->length(),
                (int) $this->handler()->areEqual(
                    $fromDb->get(0),
                    $assign,
                    [$this->handler()->formIdField(), 'editor_id']
                )
            ])
        );
    }

    public function testCanImportAnEditDecision()
    {
        $submission = $this->createRWC2015();
        $decision = $submission->get('edit_decisions')->get(0);

        $imported = $this->getStub()->callMethod(
            'importEditDecision',
            $decision
        );

        $fromDb = Registry::get('EditDecisionsDAO')->read([
            'edit_decision_id' => Registry::get('DataMapper')->getMapping(
                'edit_decisions',
                $decision->get('edit_decision_id')->getValue()
            )
        ]);

        $this->assertSame(
            '1-1-1',
            implode('-', [
                (int) $imported,
                $fromDb->length(),
                (int) $this->handler()->areEqual(
                    $fromDb->get(0),
                    $decision,
                    [$this->handler()->formIdField(), 'editor_id']
                ),
            ])
        );
    }

    public function testCanImportAReviewRound()
    {
        $submission = $this->createRWC2015();
        $round = $submission->get('review_rounds')->get(0);

        $imported = $this->getStub()->callMethod(
            'importReviewRound',
            $round
        );

        $roundId = Registry::get('DataMapper')->getMapping(
            'review_rounds',
            $round->get('review_round_id')->getValue()
        );

        $fromDb = Registry::get('ReviewRoundsDAO')->read([
            'review_round_id' => $roundId,
        ]);

        $this->assertSame(
            '1-1-1-1-1',
            implode('-', [
                (int) $imported,
                (int) is_numeric($roundId),
                $fromDb->length(),
                (int) $this->areEqual(
                    Registry::get('DataMapper')->getMapping(
                        $this->handler()->formTableName(),
                        $submission->getId()
                    ),
                    $fromDb->get(0)->getData('submission_id')
                ),
                (int) $this->handler()->areEqual(
                    $fromDb->get(0),
                    $round,
                    ['submission_id']
                )
            ])
        );
    }

    public function testCanImportAReviewAssignment()
    {
        $submission = $this->createRWC2015();

        $assign = $submission->get('review_assignments')->get(0);

        $imported = $this->getStub()->callMethod(
            'importReviewAssignment',
            $assign
        );

        $reviewId = Registry::get('DataMapper')->getMapping(
            'review_assignments',
            $assign->get('review_id')->getValue()
        );

        $fromDb = Registry::get('ReviewAssignmentsDAO')->read([
            'review_id' => $reviewId,
        ]);

        $this->assertSame(
            '1-1-1-1-0',
            implode('-', [
                (int) $imported,
                (int) is_numeric($reviewId),
                $fromDb->length(),
                (int) $this->handler()->areEqual(
                    $fromDb->get(0),
                    $assign,
                    ['submission_id','reviewer_id','review_round_id']
                ),
                (int) $this->areEqual(
                    $fromDb->get(0)->getData('review_round_id'),
                    $assign->get('review_round_id')->getValue()
                ),
            ])
        );
    }

    public function testCanImportTheSubmissionKeywords()
    {
        $submission = $this->createRWC2015();

        $imported = $this->getStub()->callMethod(
            'importSubmissionKeywords',
            $submission
        );

        $searchObjects = $this->handler()->getDAO('search_objects')->read([
            $this->handler()
                 ->formIdField() => Registry::get('DataMapper')->getMapping(
                $this->handler()->formTableName(),
                $submission->getId()
            ),
        ]);

        $this->assertSame(
            '1-3',
            implode('-', [
                (int) $imported,
                $searchObjects->length(),
            ])
        );
    }
}
