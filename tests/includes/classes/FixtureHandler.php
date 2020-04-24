<?php

namespace BeAmado\OjsMigrator\Test;
use \BeAmado\OjsMigrator\Registry;

// mocks
use \BeAmado\OjsMigrator\Test\AnnouncementMock;
use \BeAmado\OjsMigrator\Test\GroupMock;
use \BeAmado\OjsMigrator\Test\IssueMock;
use \BeAmado\OjsMigrator\Test\JournalMock;
use \BeAmado\OjsMigrator\Test\ReviewFormMock;
use \BeAmado\OjsMigrator\Test\SectionMock;
use \BeAmado\OjsMigrator\Test\SubmissionMock;
use \BeAmado\OjsMigrator\Test\UserMock;

class FixtureHandler
{
    /**
     * @var \BeAmado\OjsMigrator\OjsScenarioHandler
     */
    private $scenario;

    public function __construct()
    {
        $this->scenario = new OjsScenarioHandler();
    }

    public function createTablesForAnnouncements()
    {
        $this->scenario->createTables([
            'announcements',
            'announcement_settings',
        ]);
    }

    public function createTablesForGroups()
    {
        $this->scenario->createTables([
            'groups',
            'group_settings',
            'group_memberships',
        ]);
    }

    public function createTablesForIssues()
    {
        $this->scenario->createTables([
            'issues',
            'issue_settings',
            'issue_files',
            'issue_galleys',
            'issue_galley_settings',
            'custom_issue_orders',
            'custom_section_orders',
        ]);
    }

    public function createTablesForJournals()
    {
        $this->scenario->createTables([
            'journals',
            'journal_settings',
            'plugin_settings',
        ]);
    }

    public function createTablesForReviewForms()
    {
        $this->scenario->createTables([
            'review_forms',
            'review_form_settings',
            'review_form_elements',
            'review_form_element_settings',
        ]);
    }

    public function createTablesForSections()
    {
        $this->scenario->createTables([
            'sections',
            'section_settings',
            'section_editors',
        ]);
    }

    public function createTablesForSubmissions()
    {
        $this->scenario->createTables([
            Registry::get('SubmissionHandler')->formTableName(),
            Registry::get('SubmissionHandler')->formTableName('published'),
            Registry::get('SubmissionHandler')->formTableName('settings'),
            Registry::get('SubmissionHandler')->formTableName('files'),
            Registry::get('SubmissionHandler')->formTableName(
                'supplementary_files'
            ),
            Registry::get('SubmissionHandler')->formTableName(
                'supp_file_settings'
            ),
            Registry::get('SubmissionHandler')->formTableName('galleys'),
            Registry::get('SubmissionHandler')->formTableName(
                'galley_settings'
            ),
            Registry::get('SubmissionHandler')->formTableName('comments'),
            Registry::get('SubmissionHandler')->formTableName(
                'search_objects'
            ),
            Registry::get('SubmissionHandler')->formTableName(
                'search_object_keywords'
            ),
            Registry::get('SubmissionHandler')->formTableName(
                'search_keyword_list'
            ),
            'authors',
            'author_settings',
            'edit_decisions',
            'edit_assignments',
            'email_log',
            'email_log_users',
            'event_log',
            'event_log_settings',
            'review_rounds',
            'review_assignments',
            'review_form_responses',
        ]);
    }

    public function createTablesForUsers()
    {
        $this->scenario->createTables([
            'users',
            'user_settings',
            'roles',
            'user_interests',
            'controlled_vocabs',
            'controlled_vocab_entries',
            'controlled_vocab_entry_settings',
        ]);
    }

    protected function getCreateTablesMethod($entityName)
    {
        return 'createTablesFor' . Registry::get('GrammarHandler')->getPlural(
            Registry::get('CaseHandler')->transformCaseTo(
                'PascalCase',
                $entityName
            )
        );
    }

    public function createTablesForEntities($entities = [])
    {
        foreach ((\is_array($entities) ? $entities : [$entities]) as $entity) {
            if (
                !\is_string($entity) ||
                !\in_array(\strtolower($entity), [
                    'announcements', 'announcement',
                    'groups', 'group',
                    'issues', 'issue',
                    'journals', 'journal',
                    'review_forms', 'review_form',
                    'sections', 'section',
                    'submissions', 'submission',
                    'users', 'user',
                ])
            )
                continue;

            $this->{$this->getCreateTablesMethod($entity)}();
        }
    }

    protected function getHandler($entityName = null)
    {
        if ($entityName == null)
            return Registry::get('EntityHandler');

        return Registry::get(
            Registry::get('CaseHandler')->transformCaseTo(
                'Pascal',
                \implode('_', array(
                    Registry::get('GrammarHandler')->getSingle(
                        \str_replace('article', 'submission', $entityName)
                    ),
                    'handler'
                ))
            )
        );
    }

    protected function mockClassname($entityName)
    {
        return Registry::get('CaseHandler')->transformCaseTo(
            'Pascal',
            Registry::get('GrammarHandler')->getSingle($entityName)
        ) . 'Mock';
    }

    protected function getMockerInstance($entityName)
    {
        if (\strpos(\strtolower($entityName), 'announcement') !== false)
            return new AnnouncementMock();
        if (\strpos(\strtolower($entityName), 'group') !== false)
            return new GroupMock();
        if (\strpos(\strtolower($entityName), 'issue') !== false)
            return new IssueMock();
        if (\strpos(\strtolower($entityName), 'journal') !== false)
            return new JournalMock();
        if (\strpos(\strtolower($entityName), 'review') !== false)
            return new ReviewFormMock();
        if (\strpos(\strtolower($entityName), 'section') !== false)
            return new SectionMock();
        if (\strpos(\strtolower($entityName), 'submission') !== false)
            return new SubmissionMock();
        if (\strpos(\strtolower($entityName), 'user') !== false)
            return new UserMock();
    }

    protected function getMock($entity, $mock)
    {
        return $this->getMockerInstance($entity)->{
            'get' . Registry::get('CaseHandler')->transformCaseTo(
                'Pascal',
                Registry::get('GrammarHandler')->getSingle($entity)
            )
        }($mock);
    }

    protected function importKeywords($submission)
    {
        if (\is_string($submission)) {
            Registry::get('IoManager')->writeToStdout(implode('', array(
                'Creating (i.e importing) the keywords for the submission',
                ' "' . $submission . '"...',
                PHP_EOL,
                PHP_EOL,
            )));

            return $this->importKeywords(
                (new SubmissionMock())->getSubmission($submission)
            );
        }

        return Registry::get('SubmissionKeywordHandler')->importKeywords(
            $submission,
            false
        );
        
    }

    public function createSingle(
        $entityName,
        $entity,
        $importWholeEntity = false,
        $subEntities = array()
    ) {
        if (
            !\is_string($entity) && 
            !\is_a($entity, \BeAmado\OjsMigrator\MyObject::class)
        )
            return false;

        if ($entityName === 'keywords')
            return $this->importKeywords($entity);

        if (\is_string($entity)) {
            Registry::get('IoManager')->writeToStdout(implode('', array(
                'Creating (i.e importing) the ',
                Registry::get('GrammarHandler')->getSingle($entityName),
                ' "' . $entity . '"...',
                PHP_EOL,
                PHP_EOL,
            )));

            return $this->createSingle(
                $entityName,
                $this->getMock($entityName, $entity),
                $importWholeEntity,
                $subEntities
            );
        }

        if ($importWholeEntity && \in_array($entityName, array(
            'submissions', 'submission',
            'issues', 'issue',
        )))
            $this->createFiles($entityName, $entity);

        $this->createTablesForEntities([$entityName]);

        if ($importWholeEntity)
            return $this->getHandler($entityName)->import($entity);
        
        if (!$this->getHandler()->createOrUpdateInDatabase($entity))
            return false;

        foreach ($subEntities as $subEntity) {
            if (!$entity->hasAttribute($subEntity))
                continue;

            $entity->get($subEntity)->forEachValue(function($e) {
                $this->getHandler()->createOrUpdateInDatabase(
                    $this->getHandler()->getValidData(
                        $e->get('__tableName_')->getValue(),
                        $e
                    )
                );
            });
        }
    }

    protected function listEntitiesDir()
    {
        return Registry::get('FileSystemManager')->listdir(
            Registry::get('entitiesDir')
        );
    }

    protected function clearEntitiesDir()
    {
        foreach (($this->listEntitiesDir() ?: array()) as $dir) {
            Registry::get('FileSystemManager')->removeWholeDir($dir);
        }
    }
    

    public function createSeveral(
        $data, 
        $importWholeEntity = false,
        $subEntities = array()
    ) {
        foreach ($data as $entityName => $entities) {
            $this->createTablesForEntities([$entityName]);
            foreach($entities as $entity) {
                $this->createSingle(
                    $entityName,
                    $entity,
                    $importWholeEntity,
                    $subEntities
                );
            }
        }

        $this->clearEntitiesDir();
    }

    protected function getTableName($entity)
    {
        if (
            !\is_a($entity, \BeAmado\OjsMigrator\MyObject::class) ||
            !$entity->hasAttribute('__tableName_')
        )
            return;
        
        return $entity->get('__tableName_')->getValue();
    }

    protected function getFileName($file)
    {
        if (
            !\is_a($file, \BeAmado\OjsMigrator\MyObject::class) ||
            !$file->hasAttribute('file_name')
        )
            return;
        
        return $file->get('file_name')->getValue();
    }

    protected function formContent($file)
    {
        if (\is_a($file, \BeAmado\OjsMigrator\MyObject::class))
            return $this->formContent($this->getFileName($file));
        else if (!\is_string($file))
            return;

        return 'This is the file with name "' . $file . '"';
    }

    protected function formPathInEntitiesDir($file)
    {
        return $this->getHandler($this->getTableName($file))
                    ->formFilePathInEntitiesDir($this->getFileName($file));
    }

    public function createFiles($entityName, $entity)
    {
        if (
            !\is_string($entity) && 
            !$this->getHandler()->isEntity($entity)
        )
            return;

        if (\is_string($entity))
            return $this->createFiles(
                $entityName,
                $this->getMock($entityName, $entity)
            );

        if (!$entity->hasAttribute('files'))
            return;

        return $entity->get('files')->forEachValue(function($file) {
            return Registry::get('FileHandler')->write(
                $this->formPathInEntitiesDir($file),
                $this->formContent($file)
            );
        });
    }

    protected function formEntityJsonFileNameInEntitiesDir($entity)
    {
        if (\in_array($entity->getTableName(), array(
            'articles', 'article', 'submissions', 'submission',
            'issues', 'issue',
        )))
            return Registry::get('FileSystemManager')->formPath(array(
                $this->handler()->getEntityDataDir($entity),
                $entity->getId(),
                $entity->getId() . '.json',
            ));

        return Registry::get('FileSystemManager')->formPath(array(
            $this->handler()->getEntityDataDir($entity),
            $entity->getId() . '.json',
        ));
    }

    public function createEntity($entityName, $entity)
    {
        if (
            !\is_string($entity) && 
            !$this->getHandler()->isEntity($entity)
        )
            return;

        if (\is_string($entity)) {
            Registry::get('IoManager')->writeToStdout(implode('', array(
                'Creating the ',
                Registry::get('GrammarHandler')->getSingle($entityName),
                ' "' . $entity . ' in the entities directory"...',
                PHP_EOL,
                PHP_EOL,
            )));

            return $this->createEntity(
                $entityName,
                $this->getMock($entityName, $entity)
            );
        }
        
        if (\in_array($entityName, array(
            'articles', 'article', 'submissions', 'submission',
            'issues', 'issue',
        )))
            return $this->getHandler($entityName)->saveJsonData($entity) &&
                $this->createFiles($entityName, $entity);
        
        return $this->getHandler($entityName)->dumpEntity($entity);
    }

    public function createKeywords($entity, $allowArray = true)
    {
        if (\is_array($entity) && !$allowArray)
            return;
        else if (\is_array($entity))
            return \array_reduce($entity, function($carry, $e) {
                return $this->createKeywords($e, false);
            }, false);

        if (
            !\is_string($entity) &&
            !$this->getHandler()->isEntity($entity)
        )
            return;

        if (!$this->getHandler()->isEntity($entity)) {
            Registry::get('IoManager')->writeToStdout(implode('', array(
                'Creating the keywords for the submission "',
                $entity,
                '" in the entities directory...',
                PHP_EOL,
                PHP_EOL,
            )));

            return $this->createKeywords(
                (new SubmissionMock())->getSubmission($entity)
            );
        }

        if (!$entity->hasAttribute('keywords'))
            return;

        $entity->get('keywords')->forEachValue(function($obj) {
            Registry::get('JsonHandler')->dumpToFile(
                Registry::get('SubmissionKeywordHandler')
                        ->formSearchObjectFilename($obj),
                $obj
            );
        });
    }

    public function createEntities($data)
    {
        foreach ($data as $entityName => $entities) {
            foreach ($entities as $entity) {
                if ($entityName === 'keywords')
                    $this->createKeywords($entity);
                else
                    $this->createEntity($entityName, $entity);
            }
        }
    }
}
