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

    protected function createAssocHandler()
    {
        return new \BeAmado\OjsMigrator\Entity\AssocHandler();
    }

    protected function createCaseHandler()
    {
        return new \BeAmado\OjsMigrator\Util\CaseHandler();
    }

    protected function createChoiceHandler()
    {
        return new \BeAmado\OjsMigrator\Util\ChoiceHandler();
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

    protected function createEncodingHandler()
    {
        return new \BeAmado\OjsMigrator\Util\EncodingHandler();
    }

    protected function createEntityHandler()
    {
        return new \BeAmado\OjsMigrator\Entity\EntityHandler();
    }

    protected function createEntitySettingHandler()
    {
        return new \BeAmado\OjsMigrator\Entity\EntitySettingHandler();
    }

    protected function createFileHandler()
    {
        return new \BeAmado\OjsMigrator\Util\FileHandler();
    }

    protected function createGrammarHandler()
    {
        return new \BeAmado\OjsMigrator\Util\GrammarHandler();
    }

    protected function createGroupHandler()
    {
        return new \BeAmado\OjsMigrator\Entity\GroupHandler();
    }

    protected function createIndexDefinitionHandler()
    {
        return new \BeAmado\OjsMigrator\Db\IndexDefinitionHandler();
    }

    protected function createIssueFileHandler()
    {
        return new \BeAmado\OjsMigrator\Entity\IssueFileHandler();
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

    protected function createSerialDataHandler()
    {
        return new \BeAmado\OjsMigrator\Util\SerialDataHandler();
    }

    protected function createStatementHandler()
    {
        return new \BeAmado\OjsMigrator\Db\StatementHandler();
    }

    protected function createSubmissionCommentHandler()
    {
        return new \BeAmado\OjsMigrator\Entity\SubmissionCommentHandler();
    }

    protected function createSubmissionFileHandler()
    {
        return new \BeAmado\OjsMigrator\Entity\SubmissionFileHandler();
    }

    protected function createSubmissionHandler()
    {
        return new \BeAmado\OjsMigrator\Entity\SubmissionHandler();
    }

    protected function createSubmissionHistoryHandler()
    {
        return new \BeAmado\OjsMigrator\Entity\SubmissionHistoryHandler();
    }

    protected function createSubmissionKeywordHandler()
    {
        return new \BeAmado\OjsMigrator\Entity\SubmissionKeywordHandler();
    }

    protected function createSubmissionReviewHandler()
    {
        return new \BeAmado\OjsMigrator\Entity\SubmissionReviewHandler();
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

    protected function createDataMappingManager()
    {
        return new \BeAmado\OjsMigrator\DataMappingManager();
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

    /////////////////////////// OBJECTS //////////////////////////////////////
    protected function createArray()
    {
        return array();
    }

    protected function createBoolean($value = null)
    {
        if ($value)
            return true;
        else
            return false;
    }

    protected function createFloat($value = null)
    {
        return (float) $value;
    }

    protected function createInteger($value = null)
    {
        return (int) $value;
    }

    protected function createMyObject($value = null)
    {
        return Registry::get('MemoryManager')->create($value);
    }

    protected function createNull()
    {
        return null;
    }

    protected function createString($str)
    {
        if (\is_string($str))
            return $str;

        if (\is_numeric($str))
            return '' . $str;

        return '';
    }

    //////////////////////////////////////////////////////////////////////////

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
            case \strtolower('AssocHandler'):
                return 'AssocHandler';
            case \strtolower('CaseHandler'):
                return 'CaseHandler';
            case \strtolower('ChoiceHandler'):
                return 'ChoiceHandler';
            case \strtolower('ColumnDefinitionHandler'):
                return 'ColumnDefinitionHandler';
            case \strtolower('ConfigHandler'):
                return 'ConfigHandler';
            case \strtolower('DbHandler'):
                return 'DbHandler';
            case \strtolower('EncodingHandler'):
                return 'EncodingHandler';
            case \strtolower('EntityHandler'):
                return 'EntityHandler';
            case \strtolower('EntitySettingHandler'):
                return 'EntitySettingHandler';
            case \strtolower('FileHandler'):
                return 'FileHandler';
            case \strtolower('GrammarHandler'):
                return 'GrammarHandler';
            case \strtolower('GroupHandler'):
                return 'GroupHandler';
            case \strtolower('IndexDefinitionHandler'):
                return 'IndexDefinitionHandler';
            case \strtolower('IssueFileHandler'):
                return 'IssueFileHandler';
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
            case \strtolower('SerialDataHandler'):
                return 'SerialDataHandler';
            case \strtolower('StatementHandler'):
                return 'StatementHandler';
            case \strtolower('SubmissionCommentHandler'):
                return 'SubmissionCommentHandler';
            case \strtolower('SubmissionFileHandler'):
                return 'SubmissionFileHandler';
            case \strtolower('SubmissionHandler'):
                return 'SubmissionHandler';
            case \strtolower('SubmissionHistoryHandler'):
                return 'SubmissionHistoryHandler';
            case \strtolower('SubmissionKeywordHandler'):
                return 'SubmissionKeywordHandler';
            case \strtolower('SubmissionReviewHandler'):
                return 'SubmissionReviewHandler';
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
            case \strtolower('DataMappingManager'):
                return 'DataMappingManager';
            case \strtolower('FileSystemManager'):
                return 'FileSystemManager';
            case \strtolower('IoManager'):
                return 'IoManager';
            case \strtolower('MemoryManager'):
                return 'MemoryManager';

            //////////// MAPPERS ////////////////
            case \strtolower('DataMapper'):
                return 'DataMapper';

            //////////// OBJECTS ////////////////
            case \strtolower('Int'):
            case \strtolower('Integer'):
                return 'Integer';
            case \strtolower('Float'):
            case \strtolower('Double'):
            case \strtolower('Number'):
                return 'Float';
            case \strtolower('String'):
            case \strtolower('Str'):
                return 'String';
            case \strtolower('Boolean'):
            case \strtolower('Bool'):
                return 'Boolean';
            case \strtolower('Null'):
            case \strtolower('Nothing'):
            case \strtolower('None'):
                return 'Null';
            case \strtolower('Array'):
                return 'Array';
            case \strtolower('MyObject'):
                return 'MyObject';

            //////////// STATEMENT //////////////
            case \strtolower('Statement'):
            case \strtolower('Stmt'):
                return 'Statement';

            /////////// TIME KEEPER /////////////
            case \strtolower('TimeKeeper'):
                return 'TimeKeeper';
        }

        return $classname;
    }

    protected function createDao($tableName)
    {
        if (Registry::get('ConnectionManager')->getDbDriver() === 'sqlite')
            return new \BeAmado\OjsMigrator\Db\Sqlite\SqliteDAO($tableName);

        return new \BeAmado\OjsMigrator\Db\DAO($tableName);
    }

    protected function createDataMapper()
    {
        return new \BeAmado\OjsMigrator\DataMapper();
    }

    protected function createTimeKeeper()
    {
        return new \BeAmado\OjsMigrator\Util\TimeKeeper();
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
