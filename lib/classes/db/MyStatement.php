<?php

namespace BeAmado\OjsMigrator\Db;
use \BeAmado\OjsMigrator\MyObject;
use \BeAmado\OjsMigrator\Registry;

class MyStatement extends MyObject
{
    /**
     * 
     * @param string $query
     */
    public function __construct($query = null)
    {
        parent::__construct();
        $this->setQuery($query);
    }

    /**
     * Sets the query that will be used in the prepared statement.
     * 
     * @param string $query
     * @return void
     */
    public function setQuery($query)
    {
        if (\is_string($query))
            $this->set('query', $query);
            $this->createStmt();
    }

    /**
     * Gets the query that is used in the prepared statement.
     * 
     * @return string
     */
    public function getQuery()
    {
        if ($this->hasAttribute('query'))
            return $this->get('query')->getValue();
    }

    /**
     * Creates a new prepared statement.
     *
     * @return void
     */
    protected function createStmt()
    {
        $this->set(
            'stmt',
            Registry::get('ConnectionManager')->getConnection()
                                              ->prepare($this->getQuery())
        );
    }

    /**
     * Gets the prepared statement.
     *
     * @return \PDOStatement
     */
    protected function getStmt()
    {
        if ($this->hasAttribute('stmt'))
            return $this->get('stmt')->getValue();
    }

    protected function setParameter($name, $value)
    {
        if (!$this->hasAttribute('params'))
            $this->set('params', array());
        
        $this->get('params')->set(
            $name,
            $value
        );
    }

    protected function getParameter($name)
    {
        if (
            !$this->hasAttribute('params') ||
            !$this->get('params')->hasAttribute($name)
        ) {
            return;
        }

        return $this->get('params')->get($name)->getValue();
    }

    //protected function getParameters()
    public function getParameters()
    {
        if (!$this->hasAttribute('params'))
            return;

        return $this->get('params')->toArray();
    }

    /**
     * Binds a parameter to the prepared statement.
     *
     * @param string $name
     * @param mixed $data
     * @return boolean
     */
    protected function bindParameter($name, $data)
    {
        if ($this->getStmt()->bindParam($name, $data)) {
            $this->setParameter(
                $name,
                $data
            );

            return true;
        }

        return false;
    }

    /**
     * Binds all the parameters.
     *
     * @param \BeAmado\OjsMigrator\MyObject | array $params
     * @param \BeAmado\OjsMigrator\Entity $obj
     * @return void
     */
    public function bindParams($params, $obj)
    {
        if (\is_a($params, \BeAmado\OjsMigrator\MyObject::class))
            return $this->bindParams($params->toArray(), $obj);
        else if (!\is_array($params))
            return false;
        else if (\count($params) < 1)
            return false;

        foreach ($params as $field => $param) {
            $bound =  $this->bindParameter(
                $param,
                \is_a($obj, \BeAmado\OjsMigrator\Entity::class) 
                    ? $obj->getData($field) 
                    : $obj->get($field)->getValue()
            );

            if (!$bound)
                return false;
        }
        unset($name);
        unset($param);
        unset($bound);

        return true;
    }

    protected function fetchData()
    {
        return $this->getStmt()->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Fetches each record applying the callback function passed as argument.
     *
     * @param callable $callback
     * @return boolean
     */
    public function fetch($callback)
    {
        while ($data = $this->fetchData()) {
            if (!$callback($data))
                return false;
        }

        return true;
    }

    /**
     * Executes the prepared statement.
     *
     * @return boolean
     */
    public function execute()
    {
        return $this->getStmt()->execute();
    }

    /**
     * Wrapper for the PDOStatement::rowCount method
     *
     * @return integer
     */
    public function rowCount()
    {
        return $this->getStmt()->rowCount();
    }
}
