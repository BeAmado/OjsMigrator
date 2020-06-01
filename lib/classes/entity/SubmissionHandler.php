<?php

namespace BeAmado\OjsMigrator\Entity;
use \BeAmado\OjsMigrator\Registry;
use \BeAmado\OjsMigrator\ImportExport; // interface
use \BeAmado\OjsMigrator\EntityDirById; // trait

class SubmissionHandler extends EntityHandler implements ImportExport
{
    use EntityDirById;

    /**
     * @var string
     */
    protected $alias;
    
    public function __construct()
    {
        $this->setEntityAlias();
    }

    public function create($data, $extra = null)
    {
        return new Entity($data, $this->formTableName());
    }

    protected function setEntityAlias()
    {
        if (Registry::get('SchemaHandler')->tableIsDefined('articles'))
            $this->alias = 'article';

        if (Registry::get('SchemaHandler')->tableIsDefined('submissions'))
            $this->alias = 'submission';
    }

    public function getEntityAlias()
    {
        if (!isset($this->alias) || $this->alias == null)
            $this->setEntityAlias();

        return $this->alias;
    }

    public function formTableName($name = null)
    {
        if (
            $name == null || 
            \in_array(\strtolower($name), array(
                'main', 'article', 'articles', 'submission', 'submissions'
            ))
        )
            return $this->getEntityAlias() . 's';
        else if (\strpos(\strtolower($name), 'publish') !== false)
            return 'published_' . $this->formTableName();
        else if (!\in_array(\strtolower(\explode('_', $name)[0]), array(
            'article', 'submission'
        )))
            return $this->getEntityAlias() . '_' . $name;

        $parts = explode('_', $name);

        $parts[0] = $this->getEntityAlias();

        return \implode('_', $parts);
    }

    public function formIdField($name = null)
    {
        if ($name === 'published')
            return 'published_' . $this->formIdField();

        return $this->getEntityAlias() . '_id';
    }

    public function getDAO($name = 'main')
    {
        return Registry::get(
            Registry::get('CaseHandler')->transformCaseTo(
                'Pascal',
                $this->formTableName($name)
            ) . 'DAO'
        );
    }

    protected function registerSubmission($data)
    {
        return $this->importEntity(
            $data,
            $this->formTableName(),
            array(
                'users' => 'user_id',
                'sections' => 'section_id',
                'journals' => 'journal_id',
            ),
            true
        );
    }

    protected function importPublished($data)
    {
        return $this->importEntity(
            $data,
            $this->formTableName('published'),
            array(
                $this->formTableName() => $this->formIdField(),
                'issues' => 'issue_id',
            )
        );
    }

    protected function importSubmissionSetting($data)
    {
        return $this->importEntity(
            $data,
            $this->formTableName('settings'),
            array($this->formTableName() => $this->formIdField())
        );
    }

    protected function setMapJournalIdInRegistry($journalId)
    {
        Registry::set(
            '__mappedJournalId__',
            Registry::get('DataMapper')->getMapping(
                'journals',
                $journalId
            )
        );
    }

    protected function getJournalIdFromRegistry()
    {
        return Registry::get('__mappedJournalId__');
    }

    protected function mappedJournalIdIsSetInRegistry()
    {
        return Registry::hasKey('__mappedJournalId__');
    }

    protected function importSubmissionFiles($submission)
    {
        if (!$submission->hasAttribute('files'))
            return;

        if (!$this->mappedJournalIdIsSetInRegistry())
            $this->setMapJournalIdInRegistry(
                $submission->get('journal_id')->getValue()
            );

        return $submission->get('files')->forEachValue(function($file) {
            return Registry::get('SubmissionFileHandler')
                           ->importSubmissionFile(
                $file,
                $this->getJournalIdFromRegistry()
            );
        });
    }

    protected function importSubmissionSuppFile($data)
    {
        return $this->importEntity(
            $data,
            $this->formTableName('supplementary_files'),
            array(
                $this->formTableName() => $this->formIdField(),
                $this->formTableName('files') => 'file_id',
            )
        ) &&
        ($data->hasAttribute('settings') 
            ? $data->get('settings')->forEachValue(function($setting) {
                return $this->importEntity(
                    $setting,
                    $this->formTableName('supp_file_settings'),
                    array(
                        $this->formTableName('supplementary_files')
                            => 'supp_id',
                    )
                );
            })
            : true);
    }

    protected function importSubmissionGalley($data)
    {
        return $this->importEntity(
            $data,
            $this->formTableName('galleys'),
            array(
                $this->formTableName() => $this->formIdField(),
                $this->formTableName('files') => 'file_id',
                //$this->formTableName('files') => 'style_file_id',
            )
        ) &&
        ($data->hasAttribute('settings')
            ? $data->get('settings')->forEachValue(function($setting) {
                return $this->importEntity(
                    $setting,
                    $this->formTableName('galley_settings'),
                    array(
                        $this->formTableName('galleys') => 'galley_id',
                    )
                );
            })
            : true);
    }

    protected function importSubmissionComments($sm)
    {
        return Registry::get('SubmissionCommentHandler')->importComments($sm);
    }

    protected function importSubmissionKeywords($sm)
    {
        return Registry::get('SubmissionKeywordHandler')->importKeywords($sm);
    }

    protected function importSubmissionHistory($sm)
    {
        return Registry::get('SubmissionHistoryHandler')->importHistory($sm);
    }


    protected function importAuthor($data)
    {
        if (!$this->importEntity(
            $data,
            'authors',
            array($this->formTableName() => 'submission_id')
        ))
            return false;

        if ($data->hasAttribute('settings'))
            $data->get('settings')->forEachValue(function($setting) {
                $this->importEntity(
                    $setting,
                    'author_settings',
                    array('authors' => 'author_id')
                );
            });

        return true;
    }

    protected function importEditAssignment($data)
    {
        return $this->importEntity(
            $data,
            'edit_assignments',
            array(
                $this->formTableName() => $this->formIdField(),
                'users' => 'editor_id',
            )
        );
    }

    protected function importEditDecision($data)
    {
        return $this->importEntity(
            $data,
            'edit_decisions',
            array(
                $this->formTableName() => $this->formIdField(),
                'users' => 'editor_id',
            )
        );
    }

    protected function importReviewRound($data)
    {
        return $this->importEntity(
            $data,
            'review_rounds',
            array($this->formTableName() => 'submission_id')
        );
    }

    protected function importReviewAssignment($data)
    {
        return $this->importEntity(
            $data,
            'review_assignments',
            array(
                $this->formTableName() => 'submission_id',
                'users' => 'reviewer_id',
                $this->formTableName('files') => 'reviewer_file_id',
                'review_forms' => 'review_form_id',
                'review_rounds' => 'review_round_id',
            )
        );
    }

    protected function hasPublishedData($submission)
    {
        return $submission->hasAttribute('published') &&
            $this->isMyObject($submission->get('published')) &&
            $submission->get('published')
                       ->hasAttribute($this->formIdField('published'));
    }

    protected function publishedIdIsMapped($pubSubmission)
    {
        return Registry::get('DataMapper')->isMapped(
            $this->formTableName('published'),
            $pubSubmission->get($this->formIdField('published'))->getValue()
        );
    }

    public function importSubmission($submission)
    {
        if (
            !Registry::get('DataMapper')->isMapped(
                $this->formTableName(),
                $submission->get($this->formIdField())->getValue()
            ) &&
            !$this->registerSubmission($submission)
        )
            return false;

        // import the published submission
        if (
            $this->hasPublishedData($submission) &&
            !$this->publishedIdIsMapped($submission->get('published'))
        )
            $this->importPublished($submission->get('published'));

        // import the submission settings
        if ($submission->hasAttribute('settings'))
            $submission->get('settings')->forEachValue(function($setting) {
                return $this->importSubmissionSetting($setting);
            });

        // import the submission files
        if ($submission->hasAttribute('files'))
            $this->importSubmissionFiles($submission);
        
        // import the submission supplementary files
        if ($submission->hasAttribute('supplementary_files'))
            $submission->get('supplementary_files')
                       ->forEachValue(function($sf) {
                return $this->importSubmissionSuppFile($sf);
            });
        
        // import the submission galleys
        if ($submission->hasAttribute('galleys'))
            $submission->get('galleys')->forEachValue(function($galley) {
                return $this->importSubmissionGalley($galley);
            });


        // import the authors
        if ($submission->hasAttribute('authors'))
            $submission->get('authors')->forEachValue(function($author) {
                return $this->importAuthor($author);
            });

        // import the edit assignments
        if ($submission->hasAttribute('edit_assignments'))
            $submission->get('edit_assignments')->forEachValue(function($ea) {
                return $this->importEditAssignment($ea);
            });

        // import the edit_decisions
        if ($submission->hasAttribute('edit_decisions'))
            $submission->get('edit_decisions')->forEachValue(function($ed) {
                return $this->importEditDecision($ed);
            });

        // import the keywords
//        if ($submission->hasAttribute('keywords'))
//            $this->importSubmissionKeywords($submission);

        // import the submission history
        if ($submission->hasAttribute('history'))
            $this->importSubmissionHistory($submission);

        // import the review_rounds
        if ($submission->hasAttribute('review_rounds'))
            $submission->get('review_rounds')->forEachValue(function($rr) {
                return $this->importReviewRound($rr);
            });

        // import the review assignments
        if ($submission->hasAttribute('review_assignments'))
            $submission->get('review_assignments')->forEachValue(function($ra) {
                return $this->importReviewAssignment($ra);
            });

        // import the submission comments
        if ($submission->hasAttribute('comments'))
            $this->importSubmissionComments($submission);

        $this->updateSubmission($submission);

        return true;
    }

    public function getSubmissionId($submission)
    {
        if (\is_numeric($submission))
            return (int) $submission;

        if (
            !\is_a($submission, \BeAmado\OjsMigrator\MyObject::class) ||
            !$submission->hasAttribute($this->formIdField())
        )
            return;

        return $submission->get($this->formIdField())->getValue();
    }

    protected function getSubmissionSettings($submission)
    {
        return $this->getEntityDAO($this->formTableName('settings'))->read(
            array(
                $this->formIdField() => $this->getSubmissionId($submission)
            )
        );
    }

    protected function getPublishedSubmission($submission)
    {
        $data = $this->getEntityDAO($this->formTableName('published'))->read(
            array(
                $this->formIdField() => $this->getSubmissionId($submission)
            )
        );

        if (
            \is_a($data, \BeAmado\OjsMigrator\MyObject::class) &&
            $data->length() == 1
        )
            return $data->get(0);
    }

    protected function getSubmissionFiles($submission)
    {
        return $this->getEntityDAO($this->formTableName('files'))->read(array(
            $this->formIdField() => $this->getSubmissionId($submission)
        ));
    }

    protected function getSubmissionSupplementaryFiles($submission)
    {
        $suppFiles = $this->getEntityDAO(
            $this->formTableName('supplementary_files')
        )->read(array(
            $this->formIdField() => $this->getSubmissionId($submission)
        ));

        if (
            !\is_a($suppFiles, \BeAmado\OjsMigrator\MyObject::class) ||
            $suppFiles->length() < 1
        )
            return;

        $suppFiles->forEachValue(function($suppFile) {
            $suppFile->set(
                'settings',
                $this->getEntityDAO(
                    $this->formTableName('supp_file_settings')
                )->read(array('supp_id' => $suppFile->getData('supp_id')))
            );
        });

        return $suppFiles;
    }

    protected function getSubmissionGalleys($submission)
    {
        $galleys = $this->getEntityDAO(
            $this->formTableName('galleys')
        )->read(array(
            $this->formIdField() => $this->getSubmissionId($submission)
        ));

        if (
            !\is_a($galleys, \BeAmado\OjsMigrator\MyObject::class) ||
            $galleys->length() < 1
        )
            return;

        $galleys->forEachValue(function($galley) {
            $galley->set(
                'settings',
                $this->getEntityDAO(
                    $this->formTableName('galley_settings')
                )->read(array('galley_id' => $galley->getData('galley_id')))
            );
        });

        return $galleys;
    }

    protected function getSubmissionComments($submission)
    {
        return Registry::get(
            'SubmissionCommentHandler'
        )->getSubmissionComments($this->getSubmissionId($submission));
//        return $this->getEntityDAO(
//            $this->formTableName('comments')
//        )->read(array(
//            $this->formIdField() => $this->getSubmissionId($submission)
//        ));
    }

    protected function getSubmissionKeywords($submission)
    {
        return Registry::get(
            'SubmissionKeywordHandler'
        )->getSubmissionKeywords($this->getSubmissionId($submission));
    }

    protected function getSubmissionAuthors($submission)
    {
        $authors = Registry::get('AuthorsDAO')->read(array(
            'submission_id' => $this->getSubmissionId($submission),
        ));

        if (!\is_a($authors, \BeAmado\OjsMigrator\MyObject::class))
            return;

        $authors->forEachValue(function($author) {
            $author->set(
                'settings',
                Registry::get('AuthorSettingsDAO')->read(array(
                    'author_id' => $author->getData('author_id'),
                ))
            );
        });

        return $authors;
    }

    protected function getEditAssignments($submission)
    {
        return Registry::get('EditAssignmentsDAO')->read(array(
            $this->formIdField() => $this->getSubmissionId($submission),
        ));
    }

    protected function getEditDecisions($submission)
    {
        return Registry::get('EditDecisionsDAO')->read(array(
            $this->formIdField() => $this->getSubmissionId($submission),
        ));
    }

    protected function getSubmissionHistory($submission)
    {
        return Registry::get('SubmissionHistoryHandler')->getSubmissionHistory(
            $this->getSubmissionId($submission)
        );
    }

    protected function getReviewRounds($submission)
    {
        return Registry::get('ReviewRoundsDAO')->read(array(
            'submission_id' => $this->getSubmissionId($submission),
        ));
    }

    protected function getReviewAssignments($submission)
    {
        return Registry::get('ReviewAssignmentsDAO')->read(array(
            'submission_id' => $this->getSubmissionId($submission),
        ));
    }

    protected function getJournalId($journal)
    {
        if (\is_numeric($journal))
            return (int) $journal;

        if (
            !\is_a($journal, \BeAmado\OjsMigrator\MyObject::class) ||
            !$journal->hasAttribute('journal_id')
        )
            return;

        return $journal->get('journal_id')->getValue();
    }

    protected function copyFilesToEntitiesDir($submission)
    {
        if (
            !$submission->hasAttribute('files') ||
            !$submission->hasAttribute('journal_id') // TODO: log the problem
        )
            return;

        Registry::set(
            '__journalId__',
            $submission->get('journal_id')->getValue()
        );

        return $submission->get('files')->forEachValue(function($smFile) {
            return Registry::get('SubmissionFileHandler')
                           ->copyFileFromJournalIntoEntitiesDir(
                $smFile->get('file_name')->getValue(),
                Registry::get('__journalId__')
            );
        });
    }

    public function formSubmissionEntityDataDir($submission)
    {
        return $this->formEntityDirById(
            $this->getSubmissionId($submission),
            $this->formTableName()
        );
    }

    public function saveJsonData($submission)
    {
        if (
            !\is_a($submission, \BeAmado\OjsMigrator\MyObject::class) ||
            !$submission->hasAttribute('__tableName_') ||
            $this->entityTableName($submission) !== $this->formTableName()
        )
            return false;

        return Registry::get('JsonHandler')->dumpToFile(
            Registry::get('FileSystemManager')->formPath(array(
                $this->formSubmissionEntityDataDir(
                    $this->getSubmissionId($submission)
                ),
                $this->getSubmissionId($submission),
            )),
            $submission
        );
    }

    public function exportSubmissionsFromJournal($journal)
    {
        if (
            !\is_numeric($journal) &&
            (
                !\is_a($journal, \BeAmado\OjsMigrator\MyObject::class) ||
                !$journal->hasAttribute('journal_id')
            )
        )
            return;

        $this->getEntityDAO($this->formTableName())->dumpToJson(array(
            'journal_id' => $this->getJournalId($journal)
        ));

        foreach(Registry::get('FileSystemManager')->listdir(
            $this->getEntityDataDir($this->formTableName())
        ) as $filename) {
            $sm = Registry::get('JsonHandler')->createFromFile($filename);

            $sm->set('published', $this->getPublishedSubmission($sm));
            $sm->set('settings', $this->getSubmissionSettings($sm));
            $sm->set('files', $this->getSubmissionFiles($sm));
            $sm->set(
                'supplementary_files',
                $this->getSubmissionSupplementaryFiles($sm)
            );
            $sm->set('galleys', $this->getSubmissionGalleys($sm));
            $sm->set('comments', $this->getSubmissionComments($sm));
//            $sm->set('keywords', $this->getSubmissionKeywords($sm));
            $sm->set('authors', $this->getSubmissionAuthors($sm));
            $sm->set('edit_assignments', $this->getEditAssignments($sm));
            $sm->set('edit_decisions', $this->getEditDecisions($sm));
            $sm->set('history', $this->getSubmissionHistory($sm));
            $sm->set('review_rounds', $this->getReviewRounds($sm));
            $sm->set('review_assignments', $this->getReviewAssignments($sm));

            if ($sm->hasAttribute('files'))
                $this->copyFilesToEntitiesDir($sm);

            $this->saveJsonData($sm);
            Registry::get('FileSystemManager')->removeFile($filename);

            if (Registry::get('MigrationManager')->choseToMigrate('keywords'))
                Registry::get('SubmissionKeywordHandler')->exportSearchObjects(
                    $this->getSubmissionId($sm)
                );
        }
    }

    public function import($submission)
    {
        return $this->importSubmission($submission);
    }

    public function export($journal)
    {
        return $this->exportSubmissionsFromJournal($journal);
    }

    protected function updateSubmission($data)
    {
        return $this->importEntity(
            $data,
            $this->formTableName(),
            array(
                $this->formTableName() => $this->formIdField(),
                'users' => 'user_id',
                'sections' => 'section_id',
                'journals' => 'journal_id',
                $this->formTableName('files') => array(
                    'submission_file_id',
                    'revised_file_id',
                    'review_file_id',
                    'editor_file_id',
                ),
            )
        );
    }
}
