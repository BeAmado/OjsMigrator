<?php

namespace BeAmado\OjsMigrator\Entity;
use \BeAmado\OjsMigrator\Registry;

class EntityHandler
{
    /**
     * Forms the default value for a table column
     *
     * @param \BeAmado\OjsMigrator\Db\ColumnDefinition $colDef
     * @return mixed
     */
    protected function formDefaultValue($colDef)
    {
        if (!\is_a($colDef, \BeAmado\OjsMigrator\Db\ColumnDefinition::class))
            return;

        if ($colDef->getDefaultValue() !== null)
            return $colDef->getDefaultValue();

        if ($colDef->isNullable() || $colDef->isAutoIncrement())
            return null;

        switch(\strtolower($colDef->getSqlType())) {
            case 'date':
                return \date('Y-m-d');
            case 'datetime':
                return \date('Y-m-d H:i:s');
        }

        switch(\strtolower($colDef->getDataType())) {
            case 'string':
                return '';
            case 'integer':
            case 'float':
            case 'double':
                return 0;
        }

        return '0';
    }

    /**
     * Returns an Entity with the data necessary to conform with the table's 
     * database schema.
     *
     * @param string $name
     * @param mixed $data
     * @return \BeAmado\OjsMigrator\Entity
     */
    public function getValidData($name, $data)
    {
        $tbDef = Registry::get('SchemaHandler')->getTableDefinition($name);
        $validData = new Entity(null, $tbDef->getTableName());

        $dataObj = Registry::get('MemoryManager')->create($data);

        foreach ($tbDef->getColumnNames() as $field) {
            $validData->set(
                $field,
                ($dataObj->attributeIsNull($field))
                    ? $this->formDefaultValue($tbDef->getColumn($field))
                    : $dataObj->get($field)->getValue()
            );
        }

        Registry::get('MemoryManager')->destroy($dataObj);
        unset($dataObj);

        return $validData;
    }

    /**
     * Creates the specified entity
     *
     * @param string $name
     * @param mixed $data
     * @return \BeAmado\OjsMigrator\Entity
     */
    public function create($name, $data = null)
    {
        return $this->getValidData($name, $data);
    }

    public function isEntity($data)
    {
        return \is_a($data, \BeAmado\OjsMigrator\Entity\Entity::class);
    }

    public function isMyObject($data)
    {
        return \is_a($data, \BeAmado\OjsMigrator\MyObject::class);
    }
    
    /**
     * Checks if two entities are equal by comparing the attributes that are 
     * defined in the database schema for the specific table.
     *
     * @param \BeAmado\OjsMigrator\Entity $entity1
     * @param \BeAmado\OjsMigrator\Entity $entity2
     * @param array $disconsider
     * @param boolean $considerAutoIncrementedId
     * @return boolean
     */
    public function areEqual(
        $entity1, 
        $entity2,
        $disconsider = array(),
        $considerAutoIncrementedId = false
    ) {
        if (
            (
                !\is_array($entity1) && 
                !\is_a($entity1, \BeAmado\OjsMigrator\MyObject::class)
            ) ||
            (
                !\is_array($entity2) &&
                !\is_a($entity2, \BeAmado\OjsMigrator\MyObject::class)
            )
        )
            return;

        if (
            (\is_array($entity1) || !$this->isEntity($entity1)) && 
            \is_a($entity2, Entity::class)
        )
            return $this->areEqual(
                $this->getValidData($entity2->getTableName(), $entity1),
                $entity2,
                $disconsider,
                $considerAutoIncrementedId
            );

        if (
            (\is_array($entity2) || !$this->isEntity($entity2)) && 
            \is_a($entity1, Entity::class)
        )
            return $this->areEqual(
                $entity1,
                $this->getValidData($entity1->getTableName(), $entity2),
                $disconsider,
                $considerAutoIncrementedId
            );

        if (!\is_a($entity1, Entity::class) || !\is_a($entity2, Entity::class))
            return;

        if ($entity1->getTableName() !== $entity2->getTableName())
            return false;

        $tbDef = Registry::get('SchemaHandler')->getTableDefinition(
            $entity1->getTableName()
        );

        foreach ($tbDef->getColumnNames() as $field) {
            if (
                \in_array($field, $disconsider) ||
                (
                    !$considerAutoIncrementedId &&
                    $tbDef->getColumn($field)->isAutoIncrement()
                )
            )
                continue;

            if (
                !$entity1->hasAttribute($field) ||
                !$entity2->hasAttribute($field)
            )
                return;

            if ($entity1->getData($field) != $entity2->getData($field))
                return false;
        }

        return true;
    }
    
    /**
     * Sets the id field in the Registry for the given table
     *
     * @param string $tableName
     * @return void
     */
    protected function setIdField($tableName)
    {
        if (!\is_a(
            Registry::get('idFields'), 
            \BeAmado\OjsMigrator\MyObject::class
        )) {
            Registry::remove('idFields');
            Registry::set(
                'idFields',
                Registry::get('MemoryManager')->create()
            );
        }

        $vars = Registry::get('MemoryManager')->create();
        $vars->set(
            'tbDef',
            Registry::get('SchemaHandler')->getTableDefinition(
                $tableName
            )
        );

        foreach ($vars->get('tbDef')->getColumnNames() as $field) {
            $vars->set(
                'column',
                $vars->get('tbDef')->getColumn($field)
            );

            if (
                $vars->get('column')->isPrimaryKey() &&
                $vars->get('column')->isAutoIncrement()
            ) {
                Registry::get('idFields')->set($tableName, $field);
            }

            $vars->remove('column');
        }

        Registry::get('MemoryManager')->destroy($vars);
        unset($vars);
        
        if (isset($field))
            unset($field);
    }

    /**
     * Gets the name of the table that the entity represents
     *
     * @param mixed $entity
     * @return string
     */
    protected function entityTableName($entity)
    {
        if (
            \is_array($entity) &&
            \array_key_exists('__tableName_', $entity)
        )
            return $entity['__tableName_'];

        if (
            !$this->isEntity($entity) &&
            \is_a($entity, \BeAmado\OjsMigrator\MyObject::class) &&
            $entity->hasAttribute('__tableName_')
        )
            return $entity->get('__tableName_')->getValue();

        if (
            !\is_string($entity) &&
            !$this->isEntity($entity)
        )
            return;

        return \is_string($entity) ? $entity : $entity->getTableName();
    }

    /**
     * Gets the if of the given entity. The id is the primary key field that is
     * auto_increment.
     *
     * @param \BeAmado\OjsMigrator\Entity\Entity $entity
     * @return integer | string
     */
    public function getIdField($entity)
    {
        if (
            !\is_string($entity) &&
            !\is_a($entity, Entity::class)
        )
            return;

        if (
            !\is_a(
                Registry::get('idFields'), 
                \BeAmado\OjsMigrator\MyObject::class
            ) ||
            !Registry::get('idFields')->hasAttribute(
                $this->entityTableName($entity)
            )
        )
            $this->setIdField($this->entityTableName($entity));

        return Registry::get('idFields')->get($this->entityTableName($entity))
                                        ->getValue();
    }

    /**
     * Sets in the Registry the location where the data of the given entity 
     * must be.
     *
     * @param string | \BeAmado\OjsMigrator\Entity\Entity $entity
     * @return void
     */ 
    protected function setEntityDataDir($entity)
    {
        if (!\is_a(
            Registry::get('entitiesDataDir'), 
            \BeAmado\OjsMigrator\MyObject::class
        )) {
            Registry::remove('entitiesDataDir');
            Registry::set(
                'entitiesDataDir', 
                Registry::get('MemoryManager')->create()
            );
        }

        Registry::get('entitiesDataDir')->set(
            $this->entityTableName($entity),
            Registry::get('FileSystemManager')->formPathFromBaseDir(array(
                '_data', $this->entityTableName($entity)
            ))
        );
    }

    /**
     * Gets the location where the data of the given entity must be.
     * 
     * @param string | \BeAmado\OjsMigrator\Entity\Entity $entity
     * @return string
     */
    public function getEntityDataDir($entity)
    {
        return Registry::get('entitiesDir') 
            . \BeAmado\OjsMigrator\DIR_SEPARATOR 
            . $this->entityTableName($entity);
    }

    /**
     * Gets the DAO for the specified entity.
     * 
     * @param string | \BeAmado\OjsMigrator\Entity\Entity $entity
     * @return \BeAmado\OjsMigrator\Db\DAO
     */
    public function getEntityDAO($entity)
    {
        return Registry::get(
            Registry::get('CaseHandler')->transformCaseTo(
                'PascalCase',
                $this->entityTableName($entity) 
            ) . 'DAO'
        );
    }

    protected function entityIsOk($entity)
    {
        return \is_a($entity, \BeAmado\OjsMigrator\Entity\Entity::class) &&
            $entity->getTableName() != null &&
            (Registry::get('DataMapper')->isMappable($entity) ?
                $entity->getId() != null
                : true);
    }

    /**
     * Inserts the entity in the database and maps the id if possible.
     *
     * @param \BeAmado\OjsMigrator\Entity\Entity $entity
     * @return boolean
     */
    protected function createInDatabase($entity)
    {
        $vars = Registry::get('MemoryManager')->create();
        if (Registry::get('DataMapper')->isMappable($entity)) {
            $vars->set('oldId', $entity->getId());
            $vars->set('tableName', $entity->getTableName());
        }

        $vars->set(
            'createdEntity',
            $this->getEntityDao($entity)->create($entity)
        );
        
        if (!$this->entityIsOk($vars->get('createdEntity'))) {
            Registry::get('MemoryManager')->destroy($vars);
            unset($vars);

            return false;
        }

        if (Registry::get('DataMapper')->isMappable(
            $vars->get('createdEntity')
        ))
            Registry::get('DataMapper')->mapData(
                $vars->get('tableName')->getValue(), 
                array(
                    'old' => $vars->get('oldId')->getValue(),
                    'new' => $vars->get('createdEntity')->getId(),
                )
            );
            // TODO: treat better if did not map the id

        Registry::get('MemoryManager')->destroy($vars);
        unset($vars);

        return true;
    }

    /**
     * Updates the database table represented by the entity.
     *
     * @param \BeAmado\OjsMigrator\Entity\Entity $entity
     * @return boolean
     */
    protected function updateInDatabase($entity)
    {
        if ($this->getEntityDAO($entity)->update($entity) == 1)
            return true;

        return false;
    }

    /**
     * Creates a new registry in the database, mapping its id if necessary, or
     * updates it if already created.
     *
     * @param \BeAmado\OjsMigrator\Entity\Entity $entity
     * @return boolean
     */
    public function createOrUpdateInDatabase($entity)
    {
        if (Registry::get('DataMapper')->isMappable($entity)) {
            
            if (
                $entity->getId() == null ||
                !Registry::get('DataMapper')->isMapped(
                    $entity->getTableName(),
                    $entity->getId()
                )
            ) 
                return $this->createInDatabase($entity);

            $entity->setId(
                Registry::get('DataMapper')->getMapping(
                    $entity->getTableName(),
                    $entity->getId()
                )
            );
        }
 
        $option = 'update';
        $entities = $this->getEntityDAO($entity)->read($entity);

        if (
            !\is_a($entities, \BeAmado\OjsMigrator\MyObject::class) ||
            !\is_a($entities->get(0), \BeAmado\OjsMigrator\Entity\Entity::class)
        )
            $option = 'create';
        else if ($this->areEqual($entity, $entities->get(0)))
            $option = 'none';

        Registry::get('MemoryManager')->destroy($entities);
        unset($entities);
            
        if ($option === 'update')
            return $this->updateInDatabase($entity);
        else if ($option === 'create')
            return $this->createInDatabase($entity);
    }

    /**
     * Forms the filename of the entity data to be exported.
     * 
     * @param \BeAmado\OjsMigrator\Entity\Entity $entity
     * @return string
     */
    protected function formJsonFilename($entity)
    {
        return $this->getEntityDataDir($entity) 
            . \BeAmado\OjsMigrator\DIR_SEPARATOR . $entity->getId() . '.json';
    }

    /**
     * Dumps the entity's data into a json file.
     * 
     * @param \BeAmado\OjsMigrator\Entity\Entity $entity
     * @return boolean
     */
    public function dumpEntity($entity)
    {
        if (!$this->isEntity($entity))
            return false;

        return Registry::get('JsonHandler')->dumpToFile(
            $this->formJsonFilename($entity),
            $entity
        );
    }

    /**
     * Get the entity's primary key values as an associative array.
     *
     * @param \BeAmado\OjsMigrator\MyObject $entity
     * @param string $name
     * @return array
     */
    public function getPrimaryKeys($entity, $name = null)
    {
        if (\is_array($entity))
            $entity = Registry::get('MemoryManager')->create($entity);

        if (!$entity->hasAttribute('__tableName_') && !\is_string($name))
            return null;
            // TODO: TREAT BETTER

        $tbDef = Registry::get('SchemaHandler')->getTableDefinition(
            \is_string($name) ? $name : $entity->get('__tableName_')->getValue()
        );

        if (!\is_a($tbDef, \BeAmado\OjsMigrator\Db\TableDefinition::class))
            return;
            // TODO: TREAT BETTER

        $pks = array();
        foreach ($tbDef->getPrimaryKeys() as $pk) {
            if ($entity->hasAttribute($pk))
                $pks[$pk] = $entity->get($pk)->getValue();
            else
                $pks[$pk] = $tbDef->getColumn($pk)->getDefaultValue();
        }

        return $pks;
    }

    protected function mapAttributesUsingTable(
        $entity,
        $attributes,
        $table,
        $depth
    ) {
        if ($depth > 1)
            return false;

        if (!\is_array($attributes))
            return false;

        foreach ($attributes as $attr) {
            if (!$this->setMappedData(
                $entity,
                array($table => $attr),
                $depth + 1
            ))
                return false;
        }

        return true;
    }

    /**
     * Sets the attributes of the given entity with data mapped.
     *
     * @param \BeAmado\OjsMigrator\Entity\Entity $entity
     * @param array $args
     * @return boolean
     */
    public function setMappedData($entity, $args = array(), $depth = 0)
    {
        if (!\is_array($args))
            return;

        if (!\is_a($entity, \BeAmado\OjsMigrator\MyObject::class))
            return;

        foreach ($args as $table => $field) {
            if (\is_array($field)) {
                if ($this->mapAttributesUsingTable(
                    $entity, 
                    $field, 
                    $table, 
                    $depth
                ))
                    continue;
                else
                    return false;
            }

            if (
                !$entity->hasAttribute($field) ||
                empty($entity->get($field)->getValue())
            )
                continue;

            if (!Registry::get('DataMapper')->isMapped(
                $table,
                $entity->get($field)->getValue()
            ))
                return false;

            $entity->set(
                $field,
                Registry::get('DataMapper')->getMapping(
                    $table, 
                    $entity->get($field)->getValue()
                )
            );
        }

        return true;
    }

    /**
     * Imports the entity's data.
     *
     * @param \BeAmado\OjsMigrator\MyObject $data
     * @param string $table
     * @param array $mappings
     * @param mixed $force
     * @return boolean
     */
    protected function importEntity(
        $data, 
        $table, 
        $mappings = array(), 
        $force = false
    ) {
        $entity = $this->getValidData($table, $data);

        if (
            !empty($mappings) && 
            !$this->setMappedData($entity, $mappings)
        )
            return;

        if (\strtolower($force) === 'update')
            return $this->updateInDatabase($entity);

        if ($force)
            return $this->createInDatabase($entity);

        return $this->createOrUpdateInDatabase($entity);
    }
}
