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
}
