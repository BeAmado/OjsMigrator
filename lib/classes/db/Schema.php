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

    public function setDefinition($tablename, $def)
    {
        if (\is_a($def, TableDefinition::class)) {
            return $this->set(
                $tablename,
                $def
            );
        }

        if (\is_array($def)) {
            return $this->set(
                $tablename,
                new TableDefinition($def)
            );
        }
    }
}
