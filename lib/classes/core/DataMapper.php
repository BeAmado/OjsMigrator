<?php

namespace BeAmado\OjsMigrator;

class DataMapper
{
    public function getEntityMappingDir($entity)
    {
        return Registry::get('DataMappingDir')
            . \BeAmado\OjsMigrator\DIR_SEPARATOR 
            . (\is_a($entity, \BeAmado\OjsMigrator\Entity\Entity::class)
                ? $entity->getTableName()
                : $entity);
    }

    protected function formMappingFilename($entityName, $id)
    {
        $mappingDir = $this->getEntityMappingDir($entityName);
        $mappingRange = $this->mappingRange($entityName);
        $baseRange = Registry::get('RangeHandler')->baseRange($id);

        if (
            $mappingRange != null &&
            $baseRange != $mappingRange &&
            Registry::get('RangeHandler')->smallestRange(
                $mappingRange,
                $baseRange
            ) == $baseRange
        )
            return Registry::get('FileSystemManager')->formPath(array(
                $mappingDir,
                Registry::get('RangeHandler')->rangesDiff(
                    $mappingRange,
                    $baseRange
                ),
                Registry::get('RangeHandler')->rangesString($id)
            ));

        return Registry::get('FileSystemManager')->formPath(array(
            $this->getEntityMappingDir($entityName),
            Registry::get('RangeHandler')->rangesString($id),
        ));
    }

    public function isMapped($entityName, $id)
    {
        return Registry::get('FileSystemManager')->fileExists(
            $this->formMappingFilename($entityName, $id)
        );
    }

    public function mapData($entityName, $mapping)
    {
        if (!Registry::get('FileHandler')->write(
            $this->formMappingFilename($entityName, $mapping['old']),
            $mapping['new']
        ))
            return false;

        if (
            \count(Registry::get('FileSystemManager')->listdir(
                $this->getEntityMappingDir($entityName)
            )) > 1
        )
            return $this->restructMappingDir($entityName);

        return true;
    }

    protected function mappingRange($entityName)
    {
        if (Registry::get('FileSystemManager')->dirExists(
            $this->getEntityMappingDir($entityName)
        ))
            return \basename(Registry::get('FileSystemManager')->listdir(
                $this->getEntityMappingDir($entityName)
            )[0]);
    }

    public function getMapping($entityName, $id)
    {
        if ($this->isMapped($entityName, $id))
            return Registry::get('FileHandler')->read(
                $this->formMappingFilename($entityName, $id)
            );
    }

    protected function restructMappingDir($entityName)
    {
        $ranges = \array_map(
            'basename', 
            Registry::get('FileSystemManager')->listdir(
                $this->getEntityMappingDir($entityName)
            )
        );

        $smallest = Registry::get('RangeHandler')->smallestRange(
            $ranges[0], 
            $ranges[1]
        );

        $diff = Registry::get('RangeHandler')->rangesDiff(
            $ranges[0],
            $ranges[1]
        );


        return Registry::get('FileSystemManager')->move(
            Registry::get('FileSystemManager')->formPath(array(
                $this->getEntityMappingDir($entityName),
                $smallest
            )),
            Registry::get('FileSystemManager')->formPath(array(
                $this->getEntityMappingDir($entityName),
                $diff,
                $smallest
            ))
        );
    }

    /**
     * Checks if the given entity can have its id mapped.
     *
     * @param \BeAmado\OjsMigrator\Entity\Entity $entity
     * @return boolean
     */
    public function isMappable($entity)
    {
        if (!\is_a($entity, \BeAmado\OjsMigrator\Entity\Entity::class))
            return false;

        $tbDef = Registry::get('SchemaHandler')->getTableDefinition(
            $entity->getTableName()
        );

        if (!\is_a($tbDef, \BeAmado\OjsMigrator\Db\TableDefinition::class))
            return false;

        foreach ($tbDef->getColumnNames() as $column) {
            if ($tbDef->getColumn($column)->isAutoIncrement()) {
                Registry::get('MemoryManager')->destroy($tbDef);
                unset($tbDef);
                return true;
            }
        }

        Registry::get('MemoryManager')->destroy($tbDef);
        unset($tbDef);
        return false;
    }
}
