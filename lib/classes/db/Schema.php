<?php

namespace BeAmado\OjsMigrator\Db;
use \BeAmado\OjsMigrator\MyObject;

class Schema extends MyObject
{
    public function getDefinition($tablename)
    {
        if (!$this->hasAttribute($tablename)) {
            return;
        }

        return new TableDefinition($this->get($tablename));
    }
}
