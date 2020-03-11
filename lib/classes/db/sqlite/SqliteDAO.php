<?php

namespace BeAmado\OjsMigrator\Db\Sqlite;
use \BeAmado\OjsMigrator\Db\DAO;
use \BeAmado\OjsMigrator\Registry;
use \BeAmado\OjsMigrator\Entity\Entity;

class SqliteDAO extends DAO
{
    /**
     * @var string
     */
    private $idFieldToManualIncrement;

    public function __construct($name)
    {
        parent::__construct($name);
        if (\strpos(\strtolower($name), 'sqlite') !== false)
            return;

        if (Registry::get('QueryHandler')->hasToGetModifiedParametersForSqlite(
            $this->getTableDefinition()
        ))
            $this->markTableToBeManuallyIncremented($name);
        else
            $this->markTableNotToBeManuallyIncremented($name);

        if ($this->hasToManuallyIncrement())
            $this->setIdFieldToManuallyIncrement();
    }

    protected function getTablesManualIncrement()
    {
        if (!Registry::hasKey('tablesManualIncrement'))
            Registry::set(
                'tablesManualIncrement',
                Registry::get('MemoryManager')->create(array())
            );

        return Registry::get('tablesManualIncrement');
    }

    protected function isMarkedToBeManuallyIncremented($table)
    {
        return $this->getTablesManualIncrement()->get($table)->getValue();
    }

    protected function tableIsMarked($table)
    {
        return $this->getTablesManualIncrement()->hasAttribute($table);
    }

    protected function markTableToBeManuallyIncremented($table)
    {
        if (!$this->tableIsMarked($table))
            $this->getTablesManualIncrement()->set($table, true);
    }

    protected function markTableNotToBeManuallyIncremented($table)
    {
        if (!$this->tableIsMarked($table))
            $this->getTablesManualIncrement()->set($table, false);
    }

    protected function hasToManuallyIncrement()
    {
        return \in_array(
            Registry::get('ConnectionManager')->getDbDriver(), 
            array(
                'sqlite',
            )
        ) && $this->isMarkedToBeManuallyIncremented($this->getTableName());
    }

    protected function getTableDefinition()
    {
        return Registry::get('SchemaHandler')->getTableDefinition(
            $this->getTableName()
        );
    }

    protected function setIdFieldToManuallyIncrement()
    {
        $this->idFieldToManuallyIncrement = \array_reduce(
            $this->getTableDefinition()->getPrimaryKeyDefinitions(),
            function($carry, $column) {
                return $carry . (
                    $column->isAutoIncrement() ? $column->getColumnName() : ''
                );
            },
            ''
        );
    }

    protected function getIdFieldToIncrement()
    {   
        return $this->idFieldToManuallyIncrement;
    }

    protected function lastIdStatementName()
    {
        return 'getLastIdFor' . Registry::get('CaseHandler')->transformCaseTo(
            'Pascal',
            $this->getTableName()
        );
    }

    protected function createStatementGetLastId()
    {
        Registry::set(
            $this->lastIdStatementName(),
            Registry::get('StatementHandler')->create(
                'SELECT ' . $this->getIdFieldToIncrement() . ' AS id'
                    . ' FROM ' . $this->getTableName()
                    . ' ORDER BY ' . $this->getIdFieldToIncrement()
                    . ' DESC LIMIT 1'
            )
        );
    }

    protected function getStatementGetLastId()
    {
        if (!Registry::hasKey($this->lastIdStatementName()))
            $this->createStatementGetLastId();
        
        return Registry::get($this->lastIdStatementName());
    }

    public function formManualIncrementedId()
    {
        Registry::remove('__lastId__');
        Registry::get('StatementHandler')->execute(
            $this->getStatementGetLastId(),
            null,
            function($res) {
                Registry::set('__lastId__', $res['id']);
            }
        );

        if (\is_numeric(Registry::get('__lastId__')))
            return Registry::get('__lastId__') + 1;

        return 1;
    }

    protected function manuallyIncrementId($entity)
    {
        if (\is_array($entity))
            $entity = Registry::get('MemoryManager')->create($entity);

        $entity->set(
            $this->getIdFieldToIncrement(),
            $this->formManualIncrementedId()
        );

        return $entity;
    }

    /**
     * Inserts the entity's data into the corresponding database table.
     *
     * @param \BeAmado\OjsMigrator\Entity\Entity | array $entity
     * @param boolean $commitOnSucess
     * @param boolean $rollbackOnError
     * @return \BeAmado\OjsMigrator\Entity\Entity
     */
    public function create(
        $entity, 
        $options = array(
            'commitOnSuccess' => false, 
            'rollbackOnError' => false,
        )
    ) {
        return parent::create(
            $this->hasToManuallyIncrement() 
                ? $this->manuallyIncrementId($entity)
                : $entity, 
            $options
        );
    }
}
