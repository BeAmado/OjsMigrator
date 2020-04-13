<?php

namespace BeAmado\OjsMigrator;

trait EntityDirById
{
    public function formEntityDirById($id, $entityName)
    {
        return \BeAmado\OjsMigrator\Registry::get(
            'FileSystemManager'
        )->formPath(array(
            \BeAmado\OjsMigrator\Registry::get('EntityHandler')
                                         ->getEntityDataDir($entityName),
            $id,
        ));
    }
}
