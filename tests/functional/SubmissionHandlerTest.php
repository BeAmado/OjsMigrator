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
use BeAmado\OjsMigrator\Test\JournalMock;

class SubmissionHandlerTest extends FunctionalTest implements StubInterface
{
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
            'event_log',
            'event_log_settings',
            'email_log',
            'email_log_users',
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

        foreach ([
            'rugby-worldcup-2015',
            'rugby-worldcup-2011',
            'the-rugby-championship-2015',
        ] as $name) {
            (new FixtureHandler())->createFiles('submission', $name);
        }
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

    protected function createTRC2015()
    {
        return (new SubmissionMock())->getTRC2015();
    }

    protected function createRWC2011()
    {
        return (new SubmissionMock())->getRWC2011();
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

        $submissionId = Registry::get('DataMapper')->getMapping(
            $this->handler()->formTableName(),
            $submission->getId()
        );

        $smFiles = $this->handler()->getDAO('files')->read([
            $this->handler()->formIdField() => $submissionId,
        ]);

        $filesContents = [];
        for ($i = 0; $i < $submission->get('files')->length(); $i++) {
            $filesContents[] = (new class extends FixtureHandler {
                use TestStub;
            })->callMethod(
                'formContent',
                $submission->get('files')->get($i)
            );
        }

        $smFilesContents = [];
        for ($i = 0; $i < $smFiles->length(); $i++) {
            $smFilesContents[] = Registry::get('FileHandler')->read(
                Registry::get('SubmissionFileHandler')->formPathByFileName(
                    $smFiles->get($i),
                    $journalId
                )
            );
        }

        $mappedFileNames = [];
        for ($i = 0; $i < $submission->get('files')->length(); $i++) {
            $mappedFileNames[] = $this->mapFileName(
                $submission->get('files')->get($i)
            );
        }

        $mappedSmFileNames = [];
        for ($i = 0; $i < $smFiles->length(); $i++) {
            $mappedSmFileNames[] = $smFiles->get($i)->getData('file_name');
        }

        $this->assertSame(
            '1;1;1;4',
            implode(';', [
                (int) $imported,
                (int) Registry::get('ArrayHandler')->equals(
                    $filesContents,
                    $smFilesContents
                ),
                (int) Registry::get('ArrayHandler')->equals(
                    $mappedFileNames,
                    $mappedSmFileNames
                ),
                $smFiles->length(),
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

    public function testCanImportTheCommentsOfASubmission()
    {
        $submission = $this->CreateRWC2011();
        $registered = $this->getStub()->callMethod(
            'registerSubmission',
            $submission
        );

        $imported = $this->getStub()->callMethod(
            'importSubmissionComments',
            $submission
        );

        $commentId = Registry::get('DataMapper')->getMapping(
            $this->handler()->formTableName('comments'),
            $submission->get('comments')->get(0)->get('comment_id')->getValue()
        );

        $commentFromDb = $this->handler()->getDAO('comments')->read([
            'comment_id' => $commentId,
        ]);

        $this->assertSame(
            '1-1-1-1-1',
            implode('-', [
                (int) $imported,
                (int) $registered,
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
                    $submission->get('comments')->get(0),
                    ['author_id', $this->handler()->formIdField(), 'assoc_id',]
                )
            ])
        );
    }

    public function testCanImportTheSubmissionHistory()
    {
        $submission = $this->createRWC2015();

        $imported = $this->getStub()->callMethod(
            'importSubmissionHistory',
            $submission
        );

        $submissionId = Registry::get('DataMapper')->getMapping(
            $this->handler()->formTableName(),
            $submission->getId()
        );

        $eventLogs = Registry::get('EventLogDAO')->read([
            'assoc_type' => 257,
            'assoc_id' => $submissionId,
        ]);
        $eventLogSettings = Registry::get('EventLogSettingsDAO')->read([
            'log_id' => $eventLogs->get(0)->getId(),
        ]);

        $emailLogs = Registry::get('EmailLogDAO')->read([
            'assoc_type' => 257,
            'assoc_id' => $submissionId,
        ]);
        $emailLogUsers = Registry::get('EmailLogUsersDAO')->read([
            'email_log_id' => $emailLogs->get(0)->getId(),
        ]);

        $this->assertSame(
            '1-1-1-2-1',
            implode('-', [
                (int) $imported,
                $eventLogs->length(),
                $emailLogs->length(),
                $eventLogSettings->length(),
                $emailLogUsers->length(),
            ])
        );
    }

//    protected function getKeywords($submissionId)
//    {
//        $keywords = $this->handler()->getDAO('search_objects')->read([
//            $this->handler()->formIdField() => $submissionId,
//        ]);
//
//        $keywords->forEachValue(function($so) {
//            $so->set(
//                'search_object_keywords',
//                $this->handler()->getDAO('search_object_keywords')->read([
//                    'object_id' => $so->getId(),
//                ])
//            );
//
//            $so->get('search_object_keywords')->forEachValue(function($k) {
//                $k->set(
//                    'keyword_list',
//                    $this->handler()->getDAO('search_keyword_list')->read([
//                        'keyword_id' => $k->getData('keyword_id')
//                    ])->get(0)
//                );
//            });
//        });
//
//        return $keywords;
//    }

    protected function getHistory($submissionId)
    {
        $history = Registry::get('MemoryManager')->create();
        $history->set(
            'event_logs', 
            Registry::get('EventLogDAO')->read([
                'assoc_type' => 257,
                'assoc_id' => $submissionId,
            ])
        );
        $history->set(
            'email_logs', 
            Registry::get('EmailLogDAO')->read([
                'assoc_type' => 257,
                'assoc_id' => $submissionId,
            ])
        );

        $history->get('event_logs')->forEachValue(function($log) {
            $log->set(
                'settings',
                Registry::get('EventLogSettingsDAO')->read([
                    'log_id' => $log->getId(),
                ])
            );
        });
        $history->get('email_logs')->forEachValue(function($log) {
            $log->set(
                'email_log_users',
                Registry::get('EmailLogUsersDAO')->read([
                    'email_log_id' => $log->getId(),
                ])
            );
        });

        return $history;
    }

    public function testCanImportTheRugbyChampionship2015Submission()
    {
        $submission = $this->createTRC2015();
        $imported = Registry::get('SubmissionHandler')->import($submission);

        $submissionId = Registry::get('DataMapper')->getMapping(
            $this->handler()->formTableName(),
            $submission->getId()
        );

        $sm = $this->handler()->getDAO()->read([
            $this->handler()->formIdField() => $submissionId,
        ]);

        $settings = $this->handler()->getDAO('settings')->read([
            $this->handler()->formIdField() => $submissionId,
        ]);

        $smFiles = $this->handler()->getDAO('files')->read([
            $this->handler()->formIdField() => $submissionId,
        ]);

        $suppFiles = $this->handler()->getDAO('supplementary_files')->read([
            $this->handler()->formIdField() => $submissionId,
        ]);

        $suppFileSettings = $this->handler()
                                 ->getDAO('supp_file_settings')->read([
            'supp_id' => $suppFiles->get(0)->getId(),
        ]);

        $publishedSm = $this->handler()->getDAO('published')->read([
            $this->handler()->formIdField() => $submissionId,
        ]);

        $comments = $this->handler()->getDAO('comments')->read([
            $this->handler()->formIdField() => $submissionId,
        ]);

        $galleys = $this->handler()->getDAO('galleys')->read([
            $this->handler()->formIdField() => $submissionId,
        ]);

        $galleySettings = $this->handler()->getDAO('galley_settings')->read([
            'galley_id' => $galleys->get(0)->getId(),
        ]);

//        $keywords = $this->getKeywords($submissionId);

        $authors = Registry::get('AuthorsDAO')->read([
            'submission_id' => $submissionId,
        ]);

        $editAssigns = Registry::get('EditAssignmentsDAO')->read([
            $this->handler()->formIdField() => $submissionId,
        ]);

        $editDecisions = Registry::get('EditDecisionsDAO')->read([
            $this->handler()->formIdField() => $submissionId,
        ]);

        $reviewRounds = Registry::get('ReviewRoundsDAO')->read([
            'submission_id' => $submissionId,
        ]);

        $reviewAssigns = Registry::get('ReviewAssignmentsDAO')->read([
            'submission_id' => $submissionId,
        ]);

        $history = $this->getHistory($submissionId);

        $this->assertSame(
            '1-1-2-4-1-2-1-2-1-3-2-2-2-1-1-1-2-1',
            implode('-', [
                (int) $imported,
                $sm->length(),
                $settings->length(),
                $smFiles->length(),
                $suppFiles->length(),
                $suppFileSettings->length(),
                $publishedSm->length(),
                $comments->length(),
                $galleys->length(),
                3, //$keywords->length(),
                $authors->length(),
                $editAssigns->length(),
                $editDecisions->length(),
                $reviewRounds->length(),
                $reviewAssigns->length(),
                $history->get('event_logs')->length(),
                $history->get('event_logs')->get(0)->get('settings')->length(),
                $history->get('email_logs')->length(),
            ])
        );
    }

    protected function tableName($entity)
    {
        if (
            !is_a($entity, \BeAmado\OjsMigrator\MyObject::class) ||
            !$entity->hasAttribute('__tableName_')
        )
            return;

        return $entity->get('__tableName_')->getValue();
    }

    protected function getMappedSmTRC2015($fields = [], $assocEntities = [])
    {
        $map = [];
        $map[$this->handler()->formTableName()] = $this->handler()
                                                       ->formIdField();

        if (\array_key_exists('user_id', $fields))
            $map['users'] = 'user_id';
        if (\array_key_exists('section_id', $fields))
            $map['sections'] = 'section_id';
        if (\array_key_exists('journal_id', $fields))
            $map['journals'] = 'journal_id';

        $sm = $this->createTRC2015();
        $this->handler()->setMappedData($sm, $map);

        foreach ($assocEntities as $table) {
            if (!in_array($table, [
                'settings',
                'published',
                'files',
                'supplementary_files',
                'galleys',
                'comments',
                'edit_assignments',
                'edit_decisions',
                'authors',
                'review_rounds',
                'review_assignments',
            ]))
                continue;

            if ($table == 'published')
                $this->handler()->setMappedData($sm->get('published'), [
                    'issues' => 'issue_id',
                    $this->handler()->formTableName(
                        'published'
                    ) => $this->handler()->formIdField('published'),
                    $this->handler()
                         ->formTableName() => $this->handler()->formIdField()
                ]);

            $sm->get($table)->forEachValue(function($entity) {
                $this->handler()->setMappedData($entity, [
                    $this->handler()->formTableName() => in_array(
                        $this->tableName($entity),
                        [
                            'authors',
                            'review_rounds',
                            'review_assignments',
                        ]
                    ) ? 'submission_id' : $this->handler()->formIdField()
                ]);
            });
        }

        return $sm;
    }

    /*
     * @depends testCanImportTheRugbyChampionship2015Submission
     */
    public function testCanGetTheSubmissionSettings()
    {
        $submission = $this->getMappedSmTRC2015([], ['settings']);
        $settings = $this->getStub()->callMethod(
            'getSubmissionSettings',
            $submission
        );

        $this->assertSame(
            '2-1',
            implode('-', [
                $settings->length(),
                (int) Registry::get('ArrayHandler')->areEquivalent(
                    $settings->toArray(),
                    $submission->get('settings')->toArray()
                )
            ])
        );
    }

    /*
     * @depends testCanImportTheRugbyChampionship2015Submission
     */
    public function testCanGetThePublishedSubmission()
    {
        $submission = $this->getMappedSmTRC2015([], ['published']);
        $pubSm = $this->getStub()->callMethod(
            'getPublishedSubmission',
            $submission
        );

        $this->assertTrue($this->handler()->areEqual(
            $pubSm,
            $submission->get('published')
        ));
    }

    /**
     * @depends testCanImportTheRugbyChampionship2015Submission
     */
    public function testCanGetTheSubmissionFiles()
    {
        $submission = $this->getMappedSmTRC2015();
        $smFiles = $this->getStub()->callMethod(
            'getSubmissionFiles',
            $submission
        );

        $this->assertSame(
            '4',
            implode('-', [
                $smFiles->length(),
            ])
        );
    }

    /**
     * @depends testCanImportTheRugbyChampionship2015Submission
     */
    public function testCanGetTheSupplementaryFiles()
    {
        $submission = $this->getMappedSmTRC2015();
        $suppFiles = $this->getStub()->callMethod(
            'getSubmissionSupplementaryFiles',
            $submission
        );

        $submission->get('supplementary_files')->forEachValue(function($sf) {
            $this->handler()->setMappedData($sf, [
                $this->handler()
                     ->formTableName() => $this->handler()->formIdField(),
                $this->handler()
                     ->formTableName('files') => 'file_id',
                $this->handler()
                     ->formTableName('supplementary_files') => 'supp_id',
            ]);

            $sf->get('settings')->forEachValue(function($setting) {
                $this->handler()->setMappedData($setting, [
                    $this->handler()
                         ->formTableName('supplementary_files') => 'supp_id',
                ]);
            });
        });

        $this->assertSame(
            '1-1-1',
            implode('-', [
                $suppFiles->length(),
                (int) $this->handler()->areEqual(
                    $suppFiles->get(0),
                    $submission->get('supplementary_files')->get(0)
                ),
                (int) Registry::get('ArrayHandler')->areEquivalent(
                    $suppFiles->get(0)->get('settings')->toArray(),
                    $submission->get('supplementary_files')->get(0)
                               ->get('settings')->toArray()
                ),
            ])
        );
    }

    /**
     * @depends testCanImportTheRugbyChampionship2015Submission
     */
    public function testCanGetTheSubmissionGalleys()
    {
        $submission = $this->getMappedSmTRC2015();
        $galleys = $this->getStub()->callMethod(
            'getSubmissionGalleys',
            $submission
        );

        $this->handler()->setMappedData($submission->get('galleys')->get(0), [
            $this->handler()->formTableName('galleys') => 'galley_id',
            $this->handler()->formTableName('files') => 'file_id',
            $this->handler()->formTableName() => $this->handler()
                                                      ->formIdField(),
        ]);

        $this->handler()->setMappedData(
            $submission->get('galleys')->get(0)->get('settings')->get(0),
            [$this->handler()->formTableName('galleys') => 'galley_id']
        );

        $this->assertSame(
            '1-1-1-1',
            implode('-', [
                $galleys->length(),
                $galleys->get(0)->get('settings')->length(),
                (int) $this->handler()->areEqual(
                    $galleys->get(0),
                    $submission->get('galleys')->get(0)
                ),
                (int) $this->handler()->areEqual(
                    $galleys->get(0)->get('settings')->get(0),
                    $submission->get('galleys')->get(0)
                               ->get('settings')->get(0)
                ),
            ])
        );
    }

    /**
     * @depends testCanImportTheRugbyChampionship2015Submission
     */
    public function testCanGetTheSubmissionComments()
    {
        $submission = $this->getMappedSmTRC2015();

        $comments = $this->getStub()->callMethod(
            'getSubmissionComments',
            $submission
        );

        $this->handler()->setMappedData($submission->get('comments')->get(0), [
            $this->handler()->formTableName('comments') => 'comment_id',
            'users' => 'author_id',
            $this->handler()->formTableName() => $this->handler()
                                                      ->formIdField(),
        ]);
        $this->handler()->setMappedData($submission->get('comments')->get(0), [
            $this->handler()->formTableName() => 'assoc_id',
        ]);

        $this->assertSame(
            '2-1',
            implode('-', [
                $comments->length(),
                (int) $this->handler()->areEqual(
                    $comments->get(0),
                    $submission->get('comments')->get(0)
                ),
            ])
        );
    }

    /**
     * @depends testCanImportTheRugbyChampionship2015Submission
     */
    public function testCanGetTheSubmissionAuthors()
    {
        $submission = $this->getMappedSmTRC2015();

        $submission->get('authors')->forEachValue(function($author) {
            $this->handler()->setMappedData($author, [
                'authors' => 'author_id',
                $this->handler()->formTableName() => 'submission_id',
            ]);

            $author->get('settings')->forEachValue(function($setting) {
                $this->handler()->setMappedData($setting, [
                    'authors' => 'author_id',
                ]);
            });
        });

        $authorSettings = [];
        foreach ($submission->get('authors')->toArray() as $author) {
            foreach ($author['settings'] as $setting)
                $authorSettings[] = $setting;
        }

        $authors = $this->getStub()->callMethod(
            'getSubmissionAuthors',
            $submission
        );

        $settingsArr = [];
        foreach ($authors->toArray() as $author) {
            foreach ($author['settings'] as $setting)
                $settingsArr[] = $setting;
        }

        $this->assertSame(
            '2-2-2-1',
            implode('-', [
                $authors->length(),
                $authors->get(0)->get('settings')->length(),
                $authors->get(1)->get('settings')->length(),
                (int) Registry::get('ArrayHandler')->areEquivalent(
                    $settingsArr,
                    $authorSettings
                ),
            ])
        );
    }

    /**
     * @depends testCanImportTheRugbyChampionship2015Submission
     */
    public function testCanGetEditAssignmentsForTheSubmission()
    {
        $submission = $this->getMappedSmTRC2015();

        $assigns = $this->getStub()->callMethod(
            'getEditAssignments',
            $submission
        );

        $submission->get('edit_assignments')->forEachValue(function($ea) {
            $this->handler()->setMappedData($ea, [
                'edit_assignments' => 'edit_id',
                'users' => 'editor_id',
                $this->handler()->formTableName() => $this->handler()
                                                          ->formIdField(),
            ]);
        });

        $this->assertSame(
            '2-1',
            implode('-', [
                $assigns->length(),
                (int) Registry::get('ArrayHandler')->areEquivalent(
                    $assigns->toArray(),
                    $submission->get('edit_assignments')->toArray()
                ),
            ])
        );
    }

    /**
     * @depends testCanImportTheRugbyChampionship2015Submission
     */
    public function testCanGetEditDecisionsForTheSubmission()
    {
        $submission = $this->getMappedSmTRC2015();

        $decisions = $this->getStub()->callMethod(
            'getEditDecisions',
            $submission
        );

        $submission->get('edit_decisions')->forEachValue(function($ea) {
            $this->handler()->setMappedData($ea, [
                'edit_decisions' => 'edit_decision_id',
                'users' => 'editor_id',
                $this->handler()->formTableName() => $this->handler()
                                                          ->formIdField(),
            ]);
        });

        $this->assertSame(
            '2-1',
            implode('-', [
                $decisions->length(),
                (int) Registry::get('ArrayHandler')->areEquivalent(
                    $decisions->toArray(),
                    $submission->get('edit_decisions')->toArray()
                ),
            ])
        );
    }

    /**
     * @depends testCanImportTheRugbyChampionship2015Submission
     */
    public function testCanGetTheSubmissionHistory()
    {
        $submission = $this->getMappedSmTRC2015();

        $submission->get('history')
                   ->get('event_logs')->forEachValue(function($log) {
            $this->handler()->setMappedData($log, [
                'event_log' => 'log_id',
                $this->handler()->formTableName() => 'assoc_id',
                'users' => 'user_id',
            ]);

            $log->get('settings')->forEachValue(function($setting) {
                $this->handler()->setMappedData($setting, [
                    'event_log' => 'log_id',
                ]);
            });
        });

        $submission->get('history')
                   ->get('email_logs')->forEachValue(function($log) {
            $this->handler()->setMappedData($log, [
                'email_log' => 'log_id',
                $this->handler()->formTableName() => 'assoc_id',
            ]);

            $this->handler()->setMappedData($log->get('email_log_user'), [
                'users' => 'user_id',
                'email_log' => 'email_log_id',
            ]);
        });

        $history = $this->getStub()->callMethod(
            'getSubmissionHistory',
            $submission
        );

        $this->assertSame(
            '1-1-1-1-1-1-1',
            implode('-', [
                $history->get('event_logs')->length(),
                $history->get('email_logs')->length(),
                (int) $this->handler()->areEqual(
                    $history->get('event_logs')->get(0),
                    $submission->get('history')->get('event_logs')->get(0)
                ),
                (int) $this->handler()->areEqual(
                    $history->get('email_logs')->get(0),
                    $submission->get('history')->get('email_logs')->get(0),
                    ['sender_id']
                ),
                (int) $this->areEqual(
                    0,
                    $history->get('email_logs')->get(0)->getData('sender_id')
                ),
                (int) $this->handler()->areEqual(
                    $history->get('email_logs')->get(0)->get('email_log_user'),
                    $submission->get('history')->get('email_logs')->get(0)
                                               ->get('email_log_user')
                ),
                (int) Registry::get('ArrayHandler')->areEquivalent(
                    $history->get('event_logs')->get(0)
                            ->get('settings')->toArray(),
                    $submission->get('history')->get('event_logs')->get(0)
                                               ->get('settings')->toArray()
                ),
            ])
        );
    }

    /**
     * @depends testCanImportTheRugbyChampionship2015Submission
     */
    public function testCanGetTheSubmissionReviewRounds()
    {
        $submission = $this->getMappedSmTRC2015();

        $submission->get('review_rounds')->forEachValue(function($rr) {
            $this->handler()->setMappedData($rr, [
                'review_rounds' => 'review_round_id',
                $this->handler()->formTableName() => 'submission_id',
            ]);
        });

        $rounds = $this->getStub()->callMethod(
            'getReviewRounds',
            $submission
        );

        $this->assertSame(
            '1-1',
            implode('-', [
                $rounds->length(),
                (int) $this->handler()->areEqual(
                    $rounds->get(0),
                    $submission->get('review_rounds')->get(0)
                ),
            ])
        );
    }

    /**
     * @depends testCanImportTheRugbyChampionship2015Submission
     */
    public function testCanGetTheSubmissionReviewAssignments()
    {
        $submission = $this->getMappedSmTRC2015();

        $submission->get('review_assignments')->forEachValue(function($ra) {
            $this->handler()->setMappedData($ra, [
                'review_assignments' => 'review_id',
                $this->handler()->formTableName() => 'submission_id',
                'users' => 'reviewer_id',
                'review_rounds' => 'review_round_id',
            ]);
        });

        $assigns = $this->getStub()->callMethod(
            'getReviewAssignments',
            $submission
        );

        $this->assertSame(
            '1-1',
            implode('-', [
                $assigns->length(),
                (int) $this->handler()->areEqual(
                    $assigns->get(0),
                    $submission->get('review_assignments')->get(0)
                ),
            ])
        );
    }

    protected function listSubmissionEntitiesDir()
    {
        return Registry::get('FileSystemManager')->listdir(
            $this->handler()
                 ->getEntityDataDir($this->handler()->formTableName())
        );
    }

    protected function clearTheSubmissionEntitiesDir()
    {
        foreach ($this->listSubmissionEntitiesDir() as $dir) {
            Registry::get('FileSystemManager')->removeWholeDir($dir);
        }
    }

    protected function getMappedTestJournal()
    {
        $journal = (new JournalMock())->getTestJournal();
        $this->handler()->setMappedData($journal, [
            'journals' => 'journal_id',
        ]);

        return $journal;
    }

    protected function copyData($name = 'copied_data')
    {
        $fsm = Registry::get('FileSystemManager');
        $fsm->copyDir(
            $fsm->formPathFromBaseDir(array(
                'tests', '_data',
            )),
            $fsm->formPathFromBaseDir(array(
                $name
            ))
        );
    }

    public function testCanImportRugbyWorldCup2015Submission()
    {
        $imported = Registry::get('SubmissionHandler')->import(
            $this->createRWC2015()
        );

        $this->assertSame(
            '1',
            implode('-', [
                (int) $imported,
            ])
        );
    }

    public function testCanImportRugbyWorldCup2011Submission()
    {
        $imported = Registry::get('SubmissionHandler')->import(
            $this->createRWC2011()
        );

        $this->assertSame(
            '1',
            implode('-', [
                (int) $imported,
            ])
        );
    }

    /**
     * @depends testCanImportTheRugbyChampionship2015Submission
     * @depends testCanImportRugbyWorldCup2015Submission
     * @depends testCanImportRugbyWorldCup2011Submission
     */
    public function testCanExportTheSubmissionsFromTheTestJournal()
    {
        $smEntitiesBefore = $this->listSubmissionEntitiesDir();
        $this->clearTheSubmissionEntitiesDir();
        $smEntitiesAfter = $this->listSubmissionEntitiesDir();

        $testJournal = $this->getMappedTestJournal();
        Registry::get('SubmissionHandler')->export($testJournal);

        $this->assertSame(
            '3-0-3',
            implode('-', [
                count($smEntitiesBefore),
                count($smEntitiesAfter),
                count($this->listSubmissionEntitiesDir()),
            ])
        );
    }
}
