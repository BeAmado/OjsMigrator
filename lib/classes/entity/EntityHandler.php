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
    
    /**
     * Checks if two entities are equal by comparing the attributes that are 
     * defined in the database schema for the specific table.
     *
     * @param \BeAmado\OjsMigrator\Entity $entity1
     * @param \BeAmado\OjsMigrator\Entity $entity2
     * @param boolean $considerAutoIncrementedId
     * @return boolean
     */
    public function areEqual(
        $entity1, 
        $entity2, 
        $considerAutoIncrementedId = false
    ) {
        if (\is_array($entity1) && \is_a($entity2, Entity::class))
            return $this->areEqual(
                $this->getValidData($entity2->getTableName(), $entity1),
                $entity2
            );

        if (\is_array($entity2) && \is_a($entity1, Entity::class))
            return $this->areEqual(
                $entity1,
                $this->getValidData($entity1->getTableName(), $entity2)
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
                !$considerAutoIncrementedId &&
                $tbDef->getColumn($field)->isAutoIncrement()
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

    protected function entityTableName($entity)
    {
        if (
            !\is_string($entity) &&
            !\is_a($entity, Entity::class)
        )
            return;

        return \is_string($entity) ? $entity : $entity->getTableName();
    }

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

    public function getEntityDataDir($entity)
    {
        if (
            !\is_a(
                Registry::get('entitiesDataDir'),
                \BeAmado\OjsMigrator\MyObject::class
            ) ||
            !Registry::get('entitiesDataDir')->hasAttribute(
                $this->entityTableName($entity)
            )
        )
            $this->setEntityDataDir($entity);

        return Registry::get('entitiesDataDir')->get(
            $this->entityTableName($entity)
        )->getValue();
    }
}
