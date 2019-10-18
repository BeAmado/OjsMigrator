<?php

namespace BeAmado\OjsMigrator\Db;

class StatementHandler
{
    /**
     * Creates a statement using the query identified by the name.
     * 
     * @param string $query
     * @return boolean
     */
    public function create($query)
    {
        return new MyStatement($query);
    }

    /**
     * Executes the statement identified by the name passing the data to be 
     * bound and a callback to be executed for each record returned by the 
     * database.
     *
     * @param string $name
     * @param mixed $data
     * @param callable $callback
     */
    public function execute($name, $data, $callback = null)
    {

    }
}
