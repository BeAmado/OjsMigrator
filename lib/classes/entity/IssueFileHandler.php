<?php

namespace BeAmado\OjsMigrator\Entity;
use \BeAmado\OjsMigrator\Registry;

class IssueFileHandler extends EntityHandler
{
    public function formFilePathInEntitiesDir($filename)
    {
        return Registry::get('FileSystemManager')->formPath(array(
            $this->getEntityDataDir('issues'),
            \explode('-', $filename)[0], // issue_id
            $filename,
        ));
    }
}
