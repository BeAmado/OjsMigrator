<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Db\QueryHandler;
use BeAmado\OjsMigrator\TestStub;
use BeAmado\OjsMigrator\StubInterface;
use BeAmado\OjsMigrator\Registry;
use BeAmado\OjsMigrator\WorkWithFiles;
use BeAmado\OjsMigrator\Maestro;

class QueryHandlerTest extends TestCase implements StubInterface
{
    use WorkWithFiles;

    public function __construct()
    {
        parent::__construct();
        $this->sandbox = $this->getDataDir() . $this->sep() . 'sandbox';
    }

    protected function setUp() : void
    {
        Registry::get('FileSystemManager')->createDir($this->sandbox);
    }

    protected function tearDown() : void
    {
        Registry::get('FileSystemManager')->removeWholeDir($this->sandbox);
    }

    public static function tearDownAfterClass() : void
    {
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
        Registry::get('ArchiveManager')->tar(
            'xzf',
            $this->getDataDir() . $this->sep() . 'ojs2.tar.gz',
            $this->sandbox
        );

        $ojs2PublicHtmlDir = $this->sandbox 
            . $this->sep() . 'ojs2' 
            . $this->sep() . 'public_html';

        Maestro::setOjsDir($ojs2PublicHtmlDir);
        
        $expected = 'SELECT user_id FROM users ORDER BY user_id DESC LIMIT 1';
        $query = (new QueryHandler())->createQueryGetLast(
            Registry::get('SchemaHandler')->getTableDefinition('users')
        );

        $this->assertSame(
            $expected,
            $query
        );
    }
}
