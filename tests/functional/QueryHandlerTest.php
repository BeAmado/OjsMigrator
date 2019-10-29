<?php

use BeAmado\OjsMigrator\FunctionalTest;
use BeAmado\OjsMigrator\Db\QueryHandler;
use BeAmado\OjsMigrator\TestStub;
use BeAmado\OjsMigrator\StubInterface;
use BeAmado\OjsMigrator\Registry;
use BeAmado\OjsMigrator\WorkWithFiles;

class QueryHandlerTest extends FunctionalTest implements StubInterface
{
    use WorkWithFiles;

    public static function tearDownAfterClass() : void
    {
        parent::tearDownAfterClass();
        Registry::get('SchemaHandler')->removeSchemaDir();
    }

    public function getStub()
    {
        return new class extends QueryHandler {
            use TestStub;
        };
    }

    public function testCanGetUsersQueriesFileLocation()
    {
        $location = $this->getStub()->callMethod(
            'getFileLocation',
            'users'
        );

        $expected = \BeAmado\OjsMigrator\BASE_DIR 
            . \BeAmado\OjsMigrator\DIR_SEPARATOR . 'includes'
            . \BeAmado\OjsMigrator\DIR_SEPARATOR . 'queries'
            . \BeAmado\OjsMigrator\DIR_SEPARATOR . 'users.php';

        $this->assertSame(
            $expected,
            $location
        );
    }

    public function testCanRetrieveTheSelectUserInterestsQueryData()
    {
        $data = $this->getStub()->callMethod(
            'retrieve',
            'users-select-user_interests'
        );

        $this->assertArrayHasKey(
            'query',
            $data
        );
    }

    public function testCanGetTheSelectUserSettingsQuery()
    {
        $expected = 'SELECT us.* FROM user_settings us '
            . 'WHERE us.user_id = :selectUserSettings_userId';
        
        $query = (new QueryHandler())->getQuery('users-select-user_settings');

        $this->assertSame(
            $expected,
            $query
        );
    }

    public function testCanGetTheSelectUserRolesParameters()
    {
        $expected = array(
            'user_id'    => ':selectUserRoles_userId',
            'journal_id' => ':selectUserRoles_journalId',
        );

        $params = (new QueryHandler())->getParameters('users-select-roles');

        $this->assertEquals(
            $expected,
            $params
        );
    }

    public function testGetTheLastInsertedIdQueryForUsers()
    {
        $expected = 'SELECT user_id FROM users ORDER BY user_id DESC LIMIT 1';
        $query = Registry::get('QueRYhanDLer')->generateQueryGetLast(
            Registry::get('ScHEMAhANDler')->getTableDefinition('users')
        );

        $this->assertSame(
            $expected,
            $query
        );
    }

    public function testGetTheQueryForCreatingAuthSourcesTableInMysql()
    {
        $connData = Registry::get('ConfigHandler')->getConnectionSettings();
        if ($connData['driver'] !== 'mysql')
            $this->markTestSkipped('The driver is not mysql');

        $expected = 'CREATE TABLE `auth_sources` ('
        . '`auth_id` BIGINT AUTO_INCREMENT, '
        . '`title` VARCHAR(60) NOT NULL, '
        . '`plugin` VARCHAR(32) NOT NULL, '
        . '`auth_default` TINYINT NOT NULL DEFAULT 0, '
        . '`settings` TEXT, '
        . 'PRIMARY KEY(`auth_id`)'
        . ')';

        $query = Registry::get('QueryHandler')->generateQueryCreateTable(
            Registry::get('SchemaHandler')->getTableDefinition('auth_sources')
        );

        $this->assertSame(
            $expected,
            $query
        );
    }

    public function testGetTheQueryForCreatingAuthSourcesTableInSqlite()
    {
        $connData = Registry::get('ConfigHandler')->getConnectionSettings();
        if ($connData['driver'] !== 'sqlite')
            $this->markTestSkipped('The driver is not sqlite');
        
        $expected = 'CREATE TABLE `auth_sources` ('
        . '`auth_id` BIGINT , '
        . '`title` VARCHAR(60) NOT NULL, '
        . '`plugin` VARCHAR(32) NOT NULL, '
        . '`auth_default` TINYINT NOT NULL DEFAULT 0, '
        . '`settings` TEXT, '
        . 'PRIMARY KEY(`auth_id`)'
        . ')';

        $query = Registry::get('QueryHandler')->generateQueryCreateTable(
            Registry::get('SchemaHandler')->getTableDefinition('auth_sources')
        );

        $this->assertSame(
            $expected,
            $query
        );
    }

    public function testGetTheQueryForCreatingUserSettingsTable()
    {
        $expected = ''
          . 'CREATE TABLE `user_settings` ('
          .     '`user_id` BIGINT NOT NULL, '
          .     '`locale` VARCHAR(5) NOT NULL DEFAULT "", '
          .     '`setting_name` VARCHAR(255) NOT NULL, '
          .     '`assoc_type` BIGINT DEFAULT 0, '
          .     '`assoc_id` BIGINT DEFAULT 0, '
          .     '`setting_value` TEXT, '
          .     '`setting_type` VARCHAR(6) NOT NULL, '
          .     'PRIMARY KEY('
          .         '`user_id`, '
          .         '`locale`, '
          .         '`setting_name`, '
          .         '`assoc_type`, '
          .         '`assoc_id`'
          .     ')'
          . ')';

        $query = Registry::get('QueryHandler')->generateQueryCreateTable(
            Registry::get('SchemaHandler')->getTableDefinition('user_settings')
        );

        $this->assertSame(
            $expected,
            $query
        );
    }
}
