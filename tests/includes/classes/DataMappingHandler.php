<?php

namespace BeAmado\OjsMigrator\Test;
use \BeAmado\OjsMigrator\Registry;

class DataMappingHandler
{
    public function setDataMappingDir()
    {
        Registry::set(
            'DataMappingDir',
            Registry::get('FileSystemManager')->formPathFromBaseDir(array(
                'tests',
                '_data',
                'sandbox',
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

    public function setUpDataMappingStage($setManager = false)
    {
        $this->setDataMappingDir();
        $this->createDataMappingDir();
        if (!$setManager)
            return;

        Registry::set(
            'DataMappingManager',
            new class extends \BeAmado\OjsMigrator\DataMappingManager {
                protected function getDataMappingBaseDir()
                {
                    return (new \BeAmado\OjsMigrator\Test\DataMappingHandler())
                        ->getDataMappingDir();
                }
            }
        );
    }

    public function tearDownDataMappingStage()
    {
        $this->removeDataMappingDir();
    }
}
