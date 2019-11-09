<?php

namespace BeAmado\OjsMigrator\Db;
use \BeAmado\OjsMigrator\Registry;

class DbHandler
{
    public function getConnection()
    {
        return Registry::get('ConnectionManager')->getConnection();
    }

    public function createTable($tableName)
    {
        $query = Registry::get('QueryHandler')->generateQueryCreateTable(
            Registry::get('SchemaHandler')->getTableDefinition($tableName)
        );

        $stmt = Registry::get('StatementHandler')->create($query);

        return $stmt->execute();
    }

    /**
     * Starts a database transaction.
     * 
     * @return void
     */
    public function begin()
    {
        if (!Registry::get('ConnectionManager')->inTransaction())
            Registry::get('ConnectionManager')->beginTransaction();
    }

    /**
     * Commits the changes in a database transaction.
     *
     * @return void
     */
    public function commit()
    {
        if (Registry::get('ConnectionManager')->inTransaction())
            Registry::get('ConnectionManager')->commitTransaction();
    }

    /**
     * Rolls back a database transaction,
     *
     * @return void
     */
    public function rollback()
    {
        if (Registry::get('ConnectionManager')->inTransaction())
            Registry::get('ConnectionManager')->rollbackTransaction();
    }

    protected function tableExistsQuery()
    {
        switch(Registry::get('ConnectionManager')->getDbDriver()) {
            case 'sqlite':
                return 'SELECT COUNT(1) AS count '
                    . 'FROM sqlite_master '
                    . 'WHERE type = "table" AND name = :tableName';
        }
    }

    protected function setStatementTableExists()
    {
        Registry::remove('stmtTableExists');

        Registry::set(
            'stmtTableExists',
            Registry::get('StatementHandler')->create($this->tableExistsQuery())
        );
    }

    protected function getStatementTableExists()
    {
        if (!Registry::hasKey('stmtTableExists'))
            $this->setStatementTableExists();

        return Registry::get('stmtTableExists');
    }

    /**
     * Checks if the table exists in the database.
     *
     * @param string $tableName
     * @return boolean
     */
    public function tableExists($tableName)
    {
        if (!\is_string($tableName))
            return;

        Registry::remove('tableExistsInDb');

        Registry::get('StatementHandler')->execute(
            $this->getStatementTableExists(),
            array(
                'name' => $tableName,
            ),
            function($res) {
                Registry::set(
                    'tableExistsInDb',
                    $res['count'] >= 1
                );
            }
        );

        return Registry::get('tableExistsInDb');
    }

    public function createTableIfNotExists($tableName)
    {
        if (!\is_string($tableName))
            return;

        if (!$this->tableExists($tableName))
            $this->createTable($tableName);
    }
}
