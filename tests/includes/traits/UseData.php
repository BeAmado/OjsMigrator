<?php

namespace BeAmado\OjsMigrator\Test;
use \BeAmado\OjsMigrator\Registry;

trait UseData
{
    public function dataDir()
    {
        return Registry::get('FileSystemManager')->formPathFromBaseDir([
            'tests',
            '_data',
        ]);
    }

    public function getFromDataDir($item)
    {
        return Registry::get('FileSystemManager')->formPath(\is_array($item)
            ? \array_merge($this->dataDir(), $item)
            : [
                $this->dataDir(),
                $item
            ]
        );
    }
}
