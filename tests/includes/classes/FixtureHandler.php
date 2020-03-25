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
        foreach ($entities as $entity) {
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

    protected function getHandler($entityName)
    {
        return Registry::get(
            Registry::get('GrammarHandler')->getSingle($entityName)
            . 'handler'
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

    public function createSingle(
        $entityName,
        $entity,
        $importWholeEntity = false,
        $createTables = true
    ) {
        if (
            !\is_string($entity) && 
            !\is_a($entity, \BeAmado\OjsMigrator\MyObject::class)
        )
            return false;

        if ($createTables)
            $this->createTablesForEntities([$entityName]);

        if ($importWholeEntity)
            return $this->getHandler($entityName)->import(
                \is_string($entity) 
                    ? $this->getMock($entityName, $entity) 
                    : $entity
            );
        
        return Registry::get('EntityHandler')->createOrUpdateInDatabase(
            \is_string($entity)
                ? $this->getMock($entityName, $entity)
                : $entity
        );
    }

    public function createSeveral($data)
    {
        foreach ($data as $entityName => $entities) {
            $this->createTablesForEntities([$entityName]);
            foreach($entities as $entity) {
                $this->createSingle($entityName, $entity, false, false);
            }
        }
    }
}
