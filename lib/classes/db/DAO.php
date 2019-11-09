<?php

namespace BeAmado\OjsMigrator\Db;
use BeAmado\OjsMigrator\Registry;

class DAO
{
    /**
     * @var string
     *
     * The name of the table this DAO works upon
     */
    protected $tableName;

    /**
     *
     * @param string $name - The name of the table
     */
    public function __construct($name)
    {
        if (!\is_string($name))
            throw new \Exception('The DAO name must be a string');
            // TODO: treat better
        
        $this->tableName = $name;
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Inserts the entity's data into the corresponding database table.
     *
     * @param \BeAmado\OjsMigrator\Entity $entity
     * @param boolean $commitOnSucess
     * @param boolean $rollbackOnError
     * @return \BeAmado\OjsMigrator\Entity
     */
    public function create(
        $entity, 
        $commitOnSuccess = false, 
        $rollbackOnError = false
    ) {
        Registry::get('StatementHandler')->execute(
            'insert' . Registry::get('CaseHandler')->transformCaseTo(
                'PascalCase',
                $this->getTableName()
            ),
            $entity
        );
        
        Registry::remove('selectLastInserted');

        Registry::get('StatementHandler')->execute(
            'getlast' . Registry::get('CaseHandler')->transformCaseTo(
                'PascalCase',
                $this->getTableName()
            ),
            null,
            function($res) {
                Registry::set('selectLastInserted', $res);
            }
        );

        return Registry::get('selectLastInserted');
    }

    /**
     * Reads (SELECT) the corresponding database table data into an array of
     * entities.
     *
     * @param array $conditions
     * @return \BeAmado\OjsMigrator\MyObject
     */
    public function read($conditions = array())
    {
        Registry::remove('selectData');

        Registry::get('StatementHandler')->execute(
            'select' . Registry::get('CaseHandler')->transformCaseTo(
                'PascalCase',
                $this->getTableName()
            ),
            (\is_array($conditions) && !empty($conditions)) 
                ? $conditions 
                : null,
            function($res) {
                if (!Registry::hasKey('selectData'))
                    Registry::set(
                        'selectData',
                        Registry::get('MemoryManager')->create(array())
                    );

                Registry::get('selectData')->push(
                    Registry::get('EntityHandler')->create(
                        $this->getTableName(),
                        $res
                    )
                );

                return true; // returning true continues the iteration
            }
        );

        return Registry::get('selectData')->cloneInstance();
    }

    /**
     * Updates the corresponding database table using the given entity's data.
     *
     * @param \BeAmado\OjsMigrator\Entity $entity
     * @param array $columns
     * @param array $conditions
     * @param boolean $commitOnSuccess
     * @param boolean $rollbackOnError
     * @return boolean
     */
    public function update(
        $entity, 
        $columns = array(), 
        $conditions = array(),
        $commitOnSuccess = false,
        $rollbackOnError = false
    ) {

    }

    /**
     * Deletes the data of the corresponding database table using the given
     * conditions.
     *
     * @param array $conditions
     * @param boolean $commitOnSuccess
     * @param boolean $rollbackOnError
     * @return boolean
     */
    public function delete(
        $conditions = array(),
        $commitOnSuccess = false,
        $rollbackOnError = false
    ) {

    }
}
