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
     * Forms an array with the fields to go in the where clause.
     *
     * @param array $args
     * @param string $op
     * @return mixed
     */
    protected function formWhereFields($args, $op)
    {
        if (!\is_array($args) || !\array_key_exists('where', $args))
            return null;

        return (\in_array(
            \strtolower($op), 
            array('select', 'delete'))
        ) ? $args['where'] : null;
    }

    /**
     * Forms an array with the fields to go in the set part of an insert query.
     *
     * @param array $args
     * @return mixed
     */
    protected function formSetFields($args, $op)
    {
        if (!\is_array($args) || !\array_key_exists('set', $args))
            return null;

        return (\strtolower($op) === 'insert') ? $args['set'] : null;
    }

    /**
     * Sets the statement specified by the name.
     *
     * @param string $name
     * @param array $args
     * @return void
     */
    public function setStatement($name, $args = array())
    {
        if (Registry::hasKey($name))
            Registry::remove($name);

        $pieces = \explode(
            '_',
            Registry::get('CaseHandler')->transformCaseTo('snake', $name)
        );

        if (\count($pieces) < 2)
            return;

        $query = Registry::get('QueryHandler')->{
            (\strtolower($pieces[0]) === 'getlast') 
                ? 'generateQueryGetLast'
                : 'generateQuery' . \ucfirst(\strtolower($pieces[0]))
        }(
            Registry::get('SchemaHandler')->getTableDefinition(
                \implode('_', \array_slice($pieces, 1))
            ),
            $this->formWhereFields($args, $pieces[0]),
            $this->formSetFields($args, $pieces[0])
        );

        Registry::set(
            $name, 
            $this->create($query)
        );
    }

    /**
     * Gets the statement specified by the name.
     *
     * @param string $name
     * @param array $args
     * @return \BeAmado\OjsMigrator\Db\MyStatement
     */
    public function getStatement($name, $args = null)
    {
        if (!Registry::hasKey($name))
            $this->setStatement($name, $args);

        return Registry::get($name);
    }

    /**
     * Removes the specified statement from the Registry.
     *
     * @param string $name
     * @return boolean
     */
    public function removeStatement($name)
    {
        if (\is_a(
            Registry::get($name), 
            \BeAmado\OjsMigrator\Db\MyStatement::class
        ))
            return Registry::remove($name);
    }

    /**
     * Binds the parameters to the statement.
     *
     * @param \BeAmado\OjsMigrator\Db\MyStatement $statement
     * @param \BeAmado\OjsMigrator\MyObject $data
     * @return boolean
     */
    protected function bindParameters($statement, $data)
    {
        if (
            !\is_a($data, \BeAmado\OjsMigrator\MyObject::class) ||
            !\is_a($statement, \BeAmado\OjsMigrator\Db\MyStatement::class)
        )
            return;


        return $statement->bindParams(
            Registry::get('QueryHandler')->getParametersFromQuery(
                $statement->getQuery()
            ),
            $data->hasAttribute('payload') ? $data->get('payload') : $data
        );
    }

    /**
     * Gets the statement identified by the name and contrained by the "where"
     * and/or "set" data
     *
     * @param string $stmtName
     * @param \BeAmado\OjsMigrator\MyObject | array
     * @return \BeAmado\OjsMigrator\Db\MyStatement
     */
    protected function getProperStatement($stmtName, $data)
    {
        if ($data === null)
            return $this->getStatement($stmtName);

        if (\is_array($data))
            $data = Registry::get('MemoryManager')->create($data);
        
        if (!\is_a($data, \BeAmado\OjsMigrator\MyObject::class))
            return;

        $args = ($data->hasAttribute('where') || $data->hasAttribute('set'))
            ? array() 
            : null;

        if ($data->hasAttribute('where'))
            $args['where'] = $data->get('where')->toArray();

        if ($data->hasAttribute('set'))
            $args['set'] = $data->get('set')->toArray();

        return $this->getStatement($stmtName, $args);
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
    public function execute($stmt, $data = null, $callback = null)
    {
        if (\is_array($data))
            $data = Registry::get('MemoryManager')->create($data);

        if (
            $data !== null && 
            !\is_a($data, \BeAmado\OjsMigrator\MyObject::class)
        )
            return;
            //TODO: treat better
        
        /** @var $statement \BeAmado\OjsMigrator\Db\Statement */
        $statement = \is_a($stmt, \BeAmado\OjsMigrator\Db\MyStatement::class)
            ? $stmt
            : $this->getProperStatement($stmt, $data);

        if ($data !== null && !$this->bindParameters($statement, $data))
            return false;

        if (!$statement->execute())
            return false;

        if (\is_callable($callback))
            return $statement->fetch($callback);

        return true;
    }
}
