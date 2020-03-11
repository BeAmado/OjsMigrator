<?php

namespace BeAmado\OjsMigrator\Entity;
use \BeAmado\OjsMigrator\Registry;

class SubmissionHandler extends EntityHandler
{
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
                $submission->getData('journal_id')
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

    protected function importSubmissionComment($data)
    {
        return $this->importEntity(
            $data,
            $this->formTableName('comments'),
            array(
                $this->formTableName() => $this->formIdField(),
                'users' => 'author_id',
            )
        );
    }

    protected function importSubmissionKeywords($data)
    {
        throw new \Exception(
            'Gotta implement the method importSubmissionKeywords '
                . ' in the class SubmissionHandler.'
        );
    }

    protected function importSubmissionHistory($data)
    {
        throw new \Exception(
            'Gotta implement the method importSubmissionHistory '
                . ' in the class SubmissionHandler.'
        );
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

    public function importSubmission($submission)
    {
        if (
            !Registry::get('DataMapper')->isMapped(
                $this->formTableName(),
                $submission->getId()
            ) &&
            !$this->registerSubmission($submission)
        )
            return false;

        // import the published submission
        if (
            $submission->hasAttribute('published') &&
            !Registry::get('DataMapper')->isMapped(
                $this->formTableName('published'),
                $submission->get('published')->get(
                    $this->formIdField('published')
                )->getValue()
            )
        )
            $this->importPublished($submission->get('published'));

        // import the submission settings
        if ($submission->hasAttribute('settings'))
            $submission->get('settings')->forEachValue(function($setting) {
                $this->importSubmissionSetting($setting);
            });

        // import the submission files
        if ($submission->hasAttribute('files'))
            $this->importSubmissionFiles($submission);
        
        // import the submission supplementary files
        if ($submission->hasAttribute('supplementary_files'))
            $this->importSubmissionSuppFiles($submission);
        
        // import the submission galleys
        if ($submission->hasAttribute('galleys'))
            $submission->get('galleys')->forEachValue(function($galley) {
                $this->importSubmissionGalley($galley);
            });

        // import the submission comments
        if ($submission->hasAttribute('comments'))
            $submission->get('comments')->forEachValue(function($comment) {
                $this->importSubmissionComment($comment);
            });

        // import the keywords
        if ($submission->hasAttribute('keywords'))
            $this->importSubmissionKeywords($submission);

        // import the authors
        if ($submission->hasAttribute('authors'))
            $submission->get('authors')->forEachValue(function($author) {
                $this->importAuthor($author);
            });

        // import the edit assignments
        if ($submission->hasAttribute('edit_assignments'))
            $submission->get('edit_assignments')->forEachValue(function($ea) {
                $this->importEditAssignment($ea);
            });

        // import the edit_decisions
        if ($submission->hasAttribute('edit_decisions'))
            $submission->get('edit_decisions')->forEachValue(function($ed) {
                $this->importEditDecision($ed);
            });

        // import the submission history
        if ($submission->hasAttribute('history'))
            $this->importSubmissionHistory($submission->get('history'));

        // import the review assignments
        if ($submission->hasAttribute('review_assignments'))
            $submission->get('review_assignments')->forEachValue(function($ra) {
                $this->importReviewAssignment($ra);
            });

        // import the review_rounds
        if ($submission->hasAttribute('review_rounds'))
            $submission->get('review_rounds')->forEachValue(function($rr) {
                $this->importReviewRound($rr);
            });

        return true;
    }

    protected function getSubmissionId($submission)
    {
        if (\is_numeric($submission))
            return (int) $submission;

        if (
            !\is_a($submission, \BeAmado\OjsMigrator\MyObject::class) ||
            $submission->hasAttribute($this->getIdField())
        )
            return;

        return $submission->get($this->getIdField())->getValue();
    }

    protected function getSubmissionSettings($submission)
    {
        return $this->getEntityDAO($this->formTableName('settings'))->read(
            array(
                $this->formIdField() => $this->getSubmissionId($submission)
            )
        );
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
            $sbm = Registry::get('JsonHandler')->createFromFile($filename);

            // fetch the submission settings

            // fetch the submission files

            // fetch the submission supplementary files

            // fetch the submission galleys

            // fetch the submission comments

            // fetch the submission keywords

            // fetch the authors

            // fetch the edit assignments

            // fetch the edit decisions

            // fetch the submission history

            // fetch the review assignments

            // fetch the review rounds
        }
    }
}
