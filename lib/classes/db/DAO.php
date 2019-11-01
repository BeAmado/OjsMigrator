<?php

namespace BeAmado\OjsMigrator\Db;

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
        
    }

    /**
     * Reads (SELECT) the corresponding database table data into an array of
     * entities.
     *
     * @param array $conditions
     * @return array
     */
    public function read($conditions = array())
    {

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
