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
}
