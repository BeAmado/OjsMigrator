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

    public function testGenerateParameterNameForInsertArticlesJournalId()
    {
        $expected = ':insertArticles_journalId';

        $parameterName = $this->getStub()->callMethod(
            'generateParameterName',
            array(
                'op' => 'insert',
                'tableName' => 'articles',
                'columnName' => 'journal_id',
            )
        );

        $this->assertSame(
            $expected,
            $parameterName
        );
    }

    public function testGenerateQuerySelectJournalsWithWhereConstraint()
    {
        $expected = 'SELECT journal_id, path, seq, primary_locale, enabled '
            . 'FROM journals '
            . 'WHERE enabled = :selectJournals_enabled';

        $query = Registry::get('QueryHandler')->generateQuerySelect(
            Registry::get('SchemaHandler')->getTableDefinition('journals'),
            array('enabled')
        );

        $this->assertSame($expected, $query);
    }

    public function testGenerateQuerySelectAllRtVersions()
    {
        $expected = 'SELECT version_id, journal_id, version_key, locale, '
            . 'title, description FROM rt_versions';

        $query = Registry::get('QueryHandler')->generateQuerySelect(
            Registry::get('SchemaHandler')->getTableDefinition('rt_versions')
        );

        $this->assertSame($expected, $query);
    }

    public function testGenerateQueryUpdateQueuedPayments()
    {
        $expected = 'UPDATE queued_payments SET '
            . 'date_created = :updateQueuedPayments_dateCreated, '
            . 'date_modified = :updateQueuedPayments_dateModified, '
            . 'expiry_date = :updateQueuedPayments_expiryDate, '
            . 'payment_data = :updateQueuedPayments_paymentData '
            . 'WHERE queued_payment_id = :updateQueuedPayments_queuedPaymentId';

        $query = Registry::get('QueryHandler')->generateQueryUpdate(
            Registry::get('SchemaHandler')->getTableDefinition(
                'queued_payments'
            )
        );

        $this->assertSame(
            $expected,
            $query
        );
    }

    public function testGenerateQueryUpdateArticlesFilesIds()
    {
        $expected = 'UPDATE articles SET '
            . 'submission_file_id = :updateArticles_submissionFileId, '
            . 'revised_file_id = :updateArticles_revisedFileId, '
            . 'review_file_id = :updateArticles_reviewFileId, '
            . 'editor_file_id = :updateArticles_editorFileId '
            . 'WHERE article_id = :updateArticles_articleId';

        $query = Registry::get('QueryHandler')->generateQueryUpdate(
            Registry::get('SchemaHandler')->getTableDefinition('articles'),
            array(
                'submission_file_id', 
                'revised_file_id', 
                'review_file_id',
                'editor_file_id', 
            )
        );

        $this->assertSame(
            $expected,
            $query
        );
    }

    public function testGenerateQueryUpdateSectionReviewFormAndSeqByJournal()
    {
        $expected = 'UPDATE sections SET '
            . 'review_form_id = :updateSections_reviewFormId, '
            . 'seq = :updateSections_seq '
            . 'WHERE journal_id = :updateSections_journalId';

        $query = Registry::get('QueryHandler')->generateQueryUpdate(
            Registry::get('SchemaHandler')->getTableDefinition('sections'),
            array('review_form_id', 'seq'),
            array('journal_id')
        );

        $this->assertSame($expected, $query);
    }

    public function testGenerateQueryInsertSectionSettings()
    {
        $expected = 'INSERT INTO section_settings '
          . '('
          .     'section_id, '
          .     'locale, '
          .     'setting_name, '
          .     'setting_value, '
          .     'setting_type'
          . ') VALUES ('
          .     ':insertSectionSettings_sectionId, '
          .     ':insertSectionSettings_locale, '
          .     ':insertSectionSettings_settingName, '
          .     ':insertSectionSettings_settingValue, '
          .     ':insertSectionSettings_settingType'
          . ')';

        $query = Registry::get('QueryHandler')->generateQueryInsert(
            Registry::get('SchemaHandler')->getTableDefinition(
                'section_settings'
            )
        );

        $this->assertSame($expected, $query);
    }

    public function testGenerateQueryDeleteSectionEditors()
    {
        $expected = 'DELETE FROM section_editors WHERE '
            . 'journal_id = :deleteSectionEditors_journalId AND '
            . 'section_id = :deleteSectionEditors_sectionId AND '
            . 'user_id = :deleteSectionEditors_userId';

        $query = Registry::get('QueryHandler')->generateQueryDelete(
            Registry::get('SchemaHandler')->getTableDefinition(
                'section_editors'
            )
        );

        $this->assertSame($expected, $query);
    }

    public function testGenerateQueryDeleteArticlesByJournalId()
    {
        $expected = 'DELETE FROM articles '
            . 'WHERE journal_id = :deleteArticles_journalId';

        $query = Registry::get('QueryHandler')->generateQueryDelete(
            Registry::get('SchemaHandler')->getTableDefinition('articles'),
            array('journal_id')
        );

        $this->assertSame($expected, $query);
    }
}
