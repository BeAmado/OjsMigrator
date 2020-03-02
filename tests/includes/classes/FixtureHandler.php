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

    public function createTablesForSections()
    {
        $this->scenario->createTables([
            'sections',
            'section_settings',
            'section_editors',
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

    protected function createTablesForEntities($entities = [])
    {
        foreach ($entities as $entity) {
            if (!\is_string($entity))
                ;// do nothing
            else if (\strpos(\strtolower($entity), 'issue') !== false)
                $this->createTablesForIssues();
            else if (\strpos(\strtolower($entity), 'journal') !== false)
                $this->createTablesForJournals();
            else if (\strpos(\strtolower($entity), 'section') !== false)
                $this->createTablesForSections();
            else if (\strpos(\strtolower($entity), 'user') !== false)
                $this->createTablesForUsers();
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
        
    public function createUser(
        $user, 
        $importWholeUser = false, 
        $createTables = true
    ) {
        return $this->createSingle(
            'user', 
            $user, 
            $importWholeUser, 
            $createTables
        );
    }

    public function createUsers($users = [])
    {
        $this->createSeveral([
            'user' => $users,
        ]);
    }
}
