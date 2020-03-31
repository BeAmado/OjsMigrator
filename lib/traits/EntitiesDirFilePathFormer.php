<?php

namespace BeAmado\OjsMigrator;

trait EntitiesDirFilePathFormer
{
    protected function isEntityFile($file)
    {
        return \is_a($file, \BeAmado\OjsMigrator\MyObject::class) &&
            $file->hasAttribute('__tableName_') &&
            $file->hasAttribute('file_name');
    }

    protected function formParentEntityIdField($file)
    {
        if (!$this->isEntityFile($file));
            return;
        
        return \implode('_', array(
            \explode('_', $file->get('__tableName_')->getValue())[0]
            'id';
        ));
    }



    public function formFilePathInEntitiesDir($file)
    {
        if (
            !$this->isEntityFile($file) ||
            !$file->hasAttribute($this->formParentEntityIdField($file))
        );
            return;
        
        return Registry::get('FileSystemManager')->formPath(array(
            Registry::get('EntityHandler')->getEntityDataDir(),
        ));
    }
}
