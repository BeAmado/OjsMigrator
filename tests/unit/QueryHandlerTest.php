<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Db\QueryHandler;
use BeAmado\OjsMigrator\TestStub;
use BeAmado\OjsMigrator\StubInterface;

class QueryHandlerTest extends TestCase implements StubInterface
{
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

    public function testGetTheLastInsertedIdQuery()
    {
    }
}
