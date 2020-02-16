<?php

namespace BeAmado\OjsMigrator;

class Factory
{
    ///////////////////////////// HANDLERS ///////////////////////////////////
    protected function createAnnouncementHandler()
    {
        return new \BeAmado\OjsMigrator\Entity\AnnouncementHandler();
    }

    protected function createArrayHandler()
    {
        return new \BeAmado\OjsMigrator\Util\ArrayHandler();
    }

    protected function createCaseHandler()
    {
        return new \BeAmado\OjsMigrator\Util\CaseHandler();
    }

    protected function createColumnDefinitionHandler()
    {
        return new \BeAmado\OjsMigrator\Db\ColumnDefinitionHandler();
    }
    
    protected function createConfigHandler($configFile = null)
    {
        return new \BeAmado\OjsMigrator\Util\ConfigHandler($configFile);
    }

    protected function createDbHandler()
    {
        return new \BeAmado\OjsMigrator\Db\DbHandler();
    }

    protected function createEntityHandler()
    {
        return new \BeAmado\OjsMigrator\Entity\EntityHandler();
    }

    protected function createFileHandler()
    {
        return new \BeAmado\OjsMigrator\Util\FileHandler();
    }

    protected function createGroupHandler()
    {
        return new \BeAmado\OjsMigrator\Entity\GroupHandler();
    }

    protected function createIndexDefinitionHandler()
    {
        return new \BeAmado\OjsMigrator\Db\IndexDefinitionHandler();
    }

    protected function createIssueHandler()
    {
        return new \BeAmado\OjsMigrator\Entity\IssueHandler();
    }

    protected function createJournalHandler()
    {
        return new \BeAmado\OjsMigrator\Entity\JournalHandler();
    }

    protected function createJsonHandler()
    {
        return new \BeAmado\OjsMigrator\Util\JsonHandler();
    }

    protected function createMenuHandler()
    {
        return new \BeAmado\OjsMigrator\Util\MenuHandler();
    }

    protected function createQueryHandler()
    {
        return new \BeAmado\OjsMigrator\Db\QueryHandler();
    }

    protected function createRangeHandler()
    {
        return new \BeAmado\OjsMigrator\Util\RangeHandler();
    }

    protected function createReviewFormHandler()
    {
        return new \BeAmado\OjsMigrator\Entity\ReviewFormHandler();
    }

    protected function createSchemaHandler()
    {
        return new \BeAmado\OjsMigrator\Db\SchemaHandler();
    }

    protected function createSectionHandler()
    {
        return new \BeAmado\OjsMigrator\Entity\SectionHandler();
    }

    protected function createStatementHandler()
    {
        return new \BeAmado\OjsMigrator\Db\StatementHandler();
    }

    protected function createTableDefinitionHandler()
    {
        return new \BeAmado\OjsMigrator\Db\TableDefinitionHandler();
    }

    protected function createUserHandler()
    {
        return new \BeAmado\OjsMigrator\Entity\UserHandler();
    }

    protected function createXmlHandler()
    {
        return new \BeAmado\OjsMigrator\Util\XmlHandler();
    }

    protected function createZipHandler()
    {
        return new \BeAmado\OjsMigrator\Util\ZipHandler();
    }


    ////////////////////////////// MANAGERS //////////////////////////////////

    protected function createArchiveManager()
    {
        return new \BeAmado\OjsMigrator\Util\ArchiveManager();
    }

    protected function createConnectionManager()
    {
        return new \BeAmado\OjsMigrator\Db\ConnectionManager();
    }

    protected function createFileSystemManager()
    {
        return new \BeAmado\OjsMigrator\Util\FileSystemManager();
    }

    protected function createIoManager()
    {
        return new \BeAmado\OjsMigrator\Util\IoManager();
    }

    protected function createMemoryManager()
    {
        return new \BeAmado\OjsMigrator\Util\MemoryManager();
    }

    protected function createMigrationManager()
    {
        return new \BeAmado\OjsMigrator\MigrationManager();
    }

    protected function fixCase($classname)
    {
        switch (\strtolower($classname)) {
            ////////////// DAO //////////////////
            case \strtolower('DAO'):
                return 'Dao';

            //////////// HANDLERS ///////////////
            case \strtolower('AnnouncementHandler'):
                return 'AnnouncementHandler';
            case \strtolower('ArrayHandler'):
                return 'ArrayHandler';
            case \strtolower('CaseHandler'):
                return 'CaseHandler';
            case \strtolower('ColumnDefinitionHandler'):
                return 'ColumnDefinitionHandler';
            case \strtolower('ConfigHandler'):
                return 'ConfigHandler';
            case \strtolower('DbHandler'):
                return 'DbHandler';
            case \strtolower('EntityHandler'):
                return 'EntityHandler';
            case \strtolower('FileHandler'):
                return 'FileHandler';
            case \strtolower('GroupHandler'):
                return 'GroupHandler';
            case \strtolower('IndexDefinitionHandler'):
                return 'IndexDefinitionHandler';
            case \strtolower('IssueHandler'):
                return 'IssueHandler';
            case \strtolower('JournalHandler'):
                return 'JournalHandler';
            case \strtolower('JsonHandler'):
                return 'JsonHandler';
            case \strtolower('MenuHandler'):
                return 'MenuHandler';
            case \strtolower('QueryHandler'):
                return 'QueryHandler';
            case \strtolower('RangeHandler'):
                return 'RangeHandler';
            case \strtolower('ReviewFormHandler'):
                return 'ReviewFormHandler';
            case \strtolower('SchemaHandler'):
                return 'SchemaHandler';
            case \strtolower('SectionHandler'):
                return 'SectionHandler';
            case \strtolower('StatementHandler'):
                return 'StatementHandler';
            case \strtolower('UserHandler'):
                return 'UserHandler';
            case \strtolower('TableDefinitionHandler'):
                return 'TableDefinitionHandler';
            case \strtolower('XmlHandler'):
                return 'XmlHandler';
            case \strtolower('ZipHandler'):
                return 'ZipHandler';

            ///////////// MANAGERS //////////////
            case \strtolower('ArchiveManager'):
                return 'ArchiveManager';
            case \strtolower('ConnectionManager'):
                return 'ConnectionManager';
            case \strtolower('FileSystemManager'):
                return 'FileSystemManager';
            case \strtolower('IoManager'):
                return 'IoManager';
            case \strtolower('MemoryManager'):
                return 'MemoryManager';

            //////////// MAPPERS ////////////////
            case \strtolower('DataMapper'):
                return 'DataMapper';

            //////////// STATEMENT //////////////
            case \strtolower('Statement'):
            case \strtolower('Stmt'):
                return 'Statement';
        }

        return $classname;
    }

    protected function createDao($tableName)
    {
        return new \BeAmado\OjsMigrator\Db\DAO($tableName);
    }

    protected function createDataMapper()
    {
        return new \BeAmado\OjsMigrator\DataMapper();
    }

    /**
     * Creates an instance of the specified class passing the parameters to
     * the class constructor.
     *
     * @param string $classname
     * @param array $args
     * @return mixed
     */
    public function create($classname, $args = null)
    {
        if (\method_exists($this, 'create' . $this->fixCase($classname)))
            return $this->{'create' . $this->fixCase($classname)}($args);
    }
}
