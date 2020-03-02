<?php

use BeAmado\OjsMigrator\Test\FunctionalTest;
use BeAmado\OjsMigrator\Registry;
use BeAmado\OjsMigrator\Db\QueryHandler;
use BeAmado\OjsMigrator\Util\ConfigHandler;

// interfaces
use BeAmado\OjsMigrator\Test\StubInterface;

// traits
use BeAmado\OjsMigrator\Test\TestStub;
use BeAmado\OjsMigrator\Test\WorkWithFiles;

class QueryHandlerTest extends FunctionalTest implements StubInterface
{
    use WorkWithFiles;

    public function getStub()
    {
        return new class extends QueryHandler {
            use TestStub;
        };
    }

    public function testGetTheLastInsertedQueryForQueuedPayments()
    {
        $expected = ''
          . 'SELECT '
          .     'queued_payment_id, '
          .     'date_created, '
          .     'date_modified, '
          .     'expiry_date, '
          .     'payment_data ' 
          . 'FROM queued_payments '
          . 'ORDER BY queued_payment_id DESC '
          . 'LIMIT 1';

        $query = Registry::get('QueRYhanDLer')->generateQueryGetLast(
            Registry::get('ScHEMAhANDler')->getTableDefinition(
                'queued_payments'
            )
        );

        $this->assertSame(
            $expected,
            $query
        );
    }

    public function testGetTheQueryForCreatingAuthSourcesTableInMysql()
    {
        if (!array_search('pdo_mysql', get_loaded_extensions()))
            $this->markTestSkipped('The driver for mysql is not present');

        $ch = Registry::get('ConfigHandler');
        $connData = $ch->getConnectionSettings();

        $originalDriver = $connData['driver'];

        if (\strtolower($originalDriver) !== 'mysql')
            Registry::set(
                'ConfigHandler',
                new class extends ConfigHandler {
                    public function getConnectionSettings()
                    {
                        $settings = parent::getConnectionSettings();
                        $settings['driver'] = 'mysql';
                        return $settings;
                    }
                }
            );

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

        if (\strtolower($originalDriver) !== 'mysql')
            Registry::set('ConfigHandler', $ch);

        $this->assertSame(
            $expected,
            $query
        );
    }

    public function testGetTheQueryForCreatingAuthSourcesTableInSqlite()
    {
        if (!array_search('pdo_sqlite', get_loaded_extensions()))
            $this->markTestSkipped('The driver for sqlite is not present');

        $ch = Registry::get('ConfigHandler');
        $connData = $ch->getConnectionSettings();

        $originalDriver = $connData['driver'];

        if (\strtolower($originalDriver) !== 'sqlite')
            Registry::set(
                'ConfigHandler',
                new class extends ConfigHandler {
                    public function getConnectionSettings()
                    {
                        $settings = parent::getConnectionSettings();
                        $settings['driver'] = 'sqlite';
                        return $settings;
                    }
                }
            );

        $expected = 'CREATE TABLE `auth_sources` ('
        . '`auth_id` INTEGER , '
        . '`title` VARCHAR(60) NOT NULL, '
        . '`plugin` VARCHAR(32) NOT NULL, '
        . '`auth_default` TINYINT NOT NULL DEFAULT 0, '
        . '`settings` TEXT, '
        . 'PRIMARY KEY(`auth_id`)'
        . ')';

        $query = Registry::get('QueryHandler')->generateQueryCreateTable(
            Registry::get('SchemaHandler')->getTableDefinition('auth_sources')
        );

        if (\strtolower($originalDriver) !== 'sqlite')
            Registry::set('ConfigHandler', $ch);

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
            (Registry::get('ConnectionManager')->getDbDriver() === 'sqlite')
                ? str_replace('BIGINT', 'INTEGER', $expected)
                : $expected,
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
            null, // where
            array( //set
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
            array('journal_id'), // where
            array('review_form_id', 'seq') // set
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

    public function testCanSeeThatQueryTypeIsInsert()
    {
        $query = 'INSERT INTO articles (journal_id) VALUES (:journalId)';
        $this->assertSame(
            'insert',
            $this->getStub()->callMethod('getQueryType', $query)
        );
    }

    public function testCanSeeThatQueryTypeIsUpdate()
    {
        $query = 'UPdate users '
            . 'SET first_name = :firstName, username = :username';
        
        $this->assertSame(
            'update',
            $this->getStub()->callMethod('getQueryType', $query)
        );
    }

    public function testCanSeeThatQueryTypeIsDelete()
    {
        $query = 'delete from articles WHERE journal_id = :journalId';

        $this->assertSame(
            'delete',
            $this->getStub()->callMethod('getQueryType', $query)
        );
    }

    public function testCanSeeThatQueryTypeIsSelect()
    {
        $query = 'select * from users';

        $this->assertSame(
            'select',
            $this->getStub()->callMethod('getQueryType', $query)
        );
    }

    public function testReturnsNullWhenTheQueryIsNotAnInsertOrSelectOrDeleteOrUpdate()
    {
        $query = 'CREATE TABLE jackson (years_making_bs INTEGER)'; 

        $this->assertNull(
            $this->getStub()->callMethod('getQueryType', $query)
        );
    } 

    public function testCanGetDataBetweenParentheses()
    {
        $str = 'Mes chansons de Helloween -> ('
             .     'A tale that wasn\'t right, '
             .     'Halloween, '
             .     'How many tears, '
             .     'Nabataea, '
             .     'Keeper of the seven keys, '
             .     'Time of the oath'
             . '). Uh lala, elles sont trop cool!';

        $expected = array(
            "A tale that wasn't right",
            'Halloween',
            'How many tears',
            'Nabataea',
            'Keeper of the seven keys',
            'Time of the oath',
        );

        $result = $this->getStub()->callMethod('getDataBetweenParens', $str);

        $this->assertEquals($expected, $result);
    }   

    public function testGetParametersFromAValidInsertQuery()
    {
        $query = 'INSERT INTO roles (user_id, journal_id, role_id) VALUES '
            . '(:userId, :journalId, :roleId)';

        $result = $this->getStub()->callMethod(
            'getParametersFromInsert',
            $query
        );

        $expected = array(
            'user_id' => ':userId',
            'journal_id' => ':journalId',
            'role_id' => ':roleId',
        );

        $this->assertEquals(
            $expected,
            $result
        );
    }

    public function testGetParametersFromInvalidInsertQueryReturnsNull()
    {
        $query = 'INSERT INTO articles (journal_id, user_id) VALUES (:userId)';

        $result = $this->getStub()->callMethod(
            'getParametersFromInsert', 
            $query
        );

        $this->assertNull($result);
    }

    public function testCanGetParametersFromInsertArticleFilesQuery()
    {
        $td = Registry::get('SchemaHandler')->getTableDefinition(
            'article_files'
        );

        $parametersInsert = $this->getStub()->callMethod(
            'generateParametersInsert',
            $td
        );

        $query = Registry::get('QueryHandler')->generateQueryInsert($td);

        $params = Registry::get('QueryHandler')->getParametersFromQuery($query);

        $this->assertEquals(
            $parametersInsert,
            $params
        );
    }

    public function testCanGetParametersFromUpdateJournalSettingsQuery()
    {
        $td = Registry::get('SchemaHandler')->getTableDefinition(
            'journal_settings'
        );
        
        $expected = array(
            'journal_id'    => ':updateJournalSettings_journalId',
            'locale'        => ':updateJournalSettings_locale',
            'setting_name'  => ':updateJournalSettings_settingName',
            'setting_value' => ':updateJournalSettings_settingValue',
        );

        $query = Registry::get('QueryHandler')->generateQueryUpdate(
            $td,
            null,
            array('setting_value')
        );
        
        $params = Registry::get('QueryHandler')->getParametersFromQuery($query);

        $this->assertEquals($expected, $params);
    }

    public function testCanGetParametersFromQuerySelectUsersByJournal()
    {
        $td = Registry::get('SchemaHandler')->getTableDefinition('users');

        $expected = array(
            'journal_id' => ':selectUsers_journalId',
        );

        $query = Registry::get('QueryHandler')->generateQuerySelect(
            $td,
            array('journal_id')
        );

        $params = Registry::get('QueryHandler')->getParametersFromQuery($query);


        $this->assertEquals($expected, $params);
    }

    public function testCanGetParametersFromQueryDeletePluginSettingsByJournal()
    {
        $td = Registry::get('SchemaHandler')->getTableDefinition(
            'plugin_settings'
        );

        $query = Registry::get('QueryHandler')->generateQueryDelete(
            $td,
            array('journal_id')
        );

        $params = Registry::get('QueryHandler')->getParametersFromQuery($query);

        $expected = array(
            'journal_id' => ':deletePluginSettings_journalId',
        );

        $this->assertEquals($expected, $params);
    }

    public function testGenerateQueryToGetLast10InsertedJournals()
    {
        $expected = 'SELECT journal_id, path, seq, primary_locale, enabled '
            . 'FROM journals ORDER BY journal_id DESC LIMIT 10';

        $query = Registry::get('QueryHandler')->generateQueryGetLast(
            Registry::get('SchemaHandler')->getTableDefinition('journals'),
            10
        );

        $this->assertSame($expected, $query);
    }
}
