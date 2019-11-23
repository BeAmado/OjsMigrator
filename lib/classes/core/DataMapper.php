<?php

namespace BeAmado\OjsMigrator;

class DataMapper
{
    public function getEntityMappingDir($entityName)
    {
        return Registry::get('DataMappingDir')
            . \BeAmado\OjsMigrator\DIR_SEPARATOR . $entityName;
    }

    protected function formMappingFilename($entityName, $id)
    {
        $mappingDir = $this->getEntityMappingDir($entityName);
        $mappingRange = Registry::get('FileSystemManager')->dirExists($mappingDir) 
            ? Registry::get('FileSystemManager')->listdir(
                $mappingDir
            )[0] 
            : null;
        $baseRange = Registry::get('RangeHandler')->baseRange($id);

        if (
            $mappingRange !== null &&
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

    public function getMapping($entityName, $id)
    {
        if ($this->isMapped($entityName, $id))
            return Registry::get('FileHandler')->read(
                $this->formMappingFilename($entityName, $id)
            );
    }

    protected function restructMappingDir($entityName)
    {
        $ranges = Registry::get('FileSystemManager')->listdir(
            $this->getEntityMappingDir($entityName)
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
}
