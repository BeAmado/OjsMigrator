<?php

namespace BeAmado\OjsMigrator\Db;
use BeAmado\OjsMigrator\Registry;

class StatementHandler
{
    /**
     * Creates a statement using the query identified by the name.
     * 
     * @param string $query
     * @return \BeAmado\OjsMigrator\Db\MyStatement
     */
    public function create($query)
    {
        return new MyStatement($query);
    }

    /**
     * Sets the statement specified by the name.
     *
     * @param string $name
     * @return void
     */
    public function setStatement($name)
    {
        if (Registry::hasKey($name))
            Registry::remove($name);

        $pieces = \explode(
            '_',
            Registry::get('CaseHandler')->transformCaseTo('snake', $name)
        );

        if (\count($pieces) < 2)
            return;

        $tbDef = Registry::get('SchemaHandler')->getTableDefinition(
            \implode('_', \array_slice($pieces, 1))
        );

        $query = Registry::get('QueryHandler')->{
            'generateQuery' . \ucfirst(\strtolower($pieces[0]))
        }($tbDef);

        Registry::set(
            $name, 
            $this->create($query)
        );
    }

    /**
     * Gets the statement specified by the name.
     *
     * @param string $name
     * @return \BeAmado\OjsMigrator\Db\MyStatement
     */
    public function getStatement($name)
    {
        if (!Registry::hasKey($name))
            $this->setStatement($name);

        return Registry::get($name);
    }

    /**
     * Executes the statement identified by the name passing the data to be 
     * bound and a callback to be executed for each record returned by the 
     * database.
     *
     * @param \BeAmado\OjsMigrator\Db\MyStatement | string $stmt
     * @param \BeAmado\OjsMigrator\MyObject $data
     * @param callable $callback
     * @return boolean
     */
    public function execute($stmt, $data, $callback = null)
    {
        /** @var $statement \BeAmado\OjsMigrator\Db\MyStatement */
        $statement = null;

        if (\is_string($stmt))
            $statement = $this->getStatement($stmt);
        else if (\is_a($stmt, \BeAmado\OjsMigrator\Db\MyStatement::class))
            $statement = $stmt;
        else 
            return;

        $params = Registry::get('QueryHandler')->getParametersFromQuery(
            $statement->getQuery()
        );

        if(!$statement->bindParams($params, $data));
            return false;

        if(!$statement->execute());
            return false;

        if (\is_callable($callback))
            return $statement->fetch($callback);

        return true;
    }
}
