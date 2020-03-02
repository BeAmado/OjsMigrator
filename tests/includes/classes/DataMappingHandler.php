<?php

namespace BeAmado\OjsMigrator;

class DataMappingHandler
{
    public function setDataMappingDir()
    {
        Registry::set(
            'DataMappingDir',
            Registry::get('FileSystemManager')->formPathFromBaseDir(array(
                'tests',
                '_data',
                'data_mapping',
            ))
        );
    }

    public function getDataMappingDir()
    {
        if (!Registry::hasKey('DataMappingDir'))
            $this->setDataMappingDir();

        return Registry::get('DataMappingDir');
    }

    public function createDataMappingDir()
    {
        if (Registry::get('FileSystemManager')->dirExists(
            $this->getDataMappingDir()
        ))
            return;

        Registry::get('FileSystemManager')->createDir(
            $this->getDataMappingDir()
        );
    }

    public function removeDataMappingDir()
    {
        Registry::get('FileSystemManager')->removeWholeDir(
            $this->getDataMappingDir()
        );
    }

    public function setUpDataMappingStage()
    {
        $this->setDataMappingDir();
        $this->createDataMappingDir();
    }

    public function tearDownDataMappingStage()
    {
        $this->removeDataMappingDir();
    }
}
