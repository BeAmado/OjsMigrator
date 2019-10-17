<?php

namespace BeAmado\OjsMigrator\Db;
use \BeAmado\OjsMigrator\MyObject;
use \BeAmado\OjsMigrator\Registry;

class MyStatement extends MyObject
{
    public function __construct($query = null)
    {
        parent::__construct();
        $this->setQuery($query);
    }

    public function setQuery($query)
    {
        if (\is_string($query))
            $this->set('query', $query);
    }

    public function getQuery()
    {
        if ($this->hasAttribute('query'))
            return $this->get('query')->getValue();
    }

    protected function create()
    {
        $this->set(
            'stmt',
            Registry::get('ConnectionManager')->getConnection()
                                              ->prepare($this->getQuery())
        );
    }

    protected function getStmt()
    {
        if ($this->hasAttribute('stmt'))
            return $this->get('stmt')->getValue();
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
        return $this->getStmt()->bindParam($name, $data);
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
        if (\is_array($params)) {
            foreach ($params as $field => $param) {
                $this->bindParameter(
                    $param,
                    $obj->get($field)->getValue()
                );
            }
            unset($name);
            unset($param);
        } elseif (\is_a($params, \BeAmado\OjsMigrator\MyObject::class)) {
            $this->bindParams(
                $params->toArray(),
                $obj
            );
        }
    }

    /**
     * Fetches each record applying the callback function passed as argument.
     *
     * @param callable $callback
     * @return void
     */
    public function fetch($callback)
    {
        while ($data = $this->getStmt()->fetch(\PDO::FETCH_ASSOC))
            $callback($data);
    }
}
