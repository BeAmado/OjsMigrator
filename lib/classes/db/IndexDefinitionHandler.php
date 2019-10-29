<?php

namespace BeAmado\OjsMigrator\Db;
use \BeAmado\OjsMigrator\Registry;

class IndexDefinitionHandler extends AbstractDefinitionHandler
{
    /**
     * Checks whether or not the given object is defining a table index.
     *
     * @param \BeAmado\OjsMigrator\MyObject $obj
     * @return boolean
     */
    public function isIndex($obj)
    {
        return $this->nameIs($obj, 'index');
    }
    
    /**
     * Gets the name of the index being defined for the table
     *
     * @param \BeAmado\OjsMigrator\MyObject $obj
     * @return string
     */
    public function getIndexName($obj)
    {
        if ($this->isIndex($obj)) 
            return $this->getAttribute($obj, 'name');
    }

    /**
     * Checks if the index is defining a column to be unique
     *
     * @param \BeAmado\OjsMigrator\MyObject | array $index
     * @return boolean
     */
    public function isUniqueColumnIndex($index)
    {
        if (!$this->isIndex($index))
            return;

        if (\is_array($index))
            return $this->isUniqueColumnIndex(
                Registry::get('MemoryManager')->create($index)
            );
        
        Registry::remove('colCount');
        Registry::remove('isUnique');
        Registry::set('colCount', 0);
        Registry::set('isUnique', false);

        $index->get('children')->forEachValue(function($child) {
            if ($this->nameIs($child, 'col'))
                Registry::increment('colCount');

            if ($this->nameIs($child, 'unique'))
                Registry::set('isUnique', true);
        });

        return Registry::get('isUnique') && Registry::get('colCount') === 1;
    }

    /**
     * Checks whether or not the given index object defines primary keys.
     *
     * @param \BeAmado\OjsMigrator\MyObject $index
     * @return boolean
     */
    public function isPkIndex($index)
    {
        return $this->isIndex($index) &&
            \strtolower(\substr($this->getIndexName($index), -4)) === 'pkey';
    }

    /**
     * Gets the columns of the index.
     *
     * @param \BeAmado\OjsMigrator\MyObject $index
     * @return array
     */
    public function getIndexColumns($index)
    {
        if (!$this->isIndex($index))
            return;

        if (\is_array($index))
            return $this->getIndexColumns(
                Registry::get('MemoryManager')->create($index)
            );
        
        Registry::remove('indexColumns');
        Registry::set(
            'indexColumns', 
            Registry::get('MemoryManager')->create()
        );

        /** @var $o \BeAmado\OjsMigrator\MyObject */
        $index->get('children')->forEachValue(function($o) {
            if ($this->nameIs($o, 'col'))
                Registry::get('indexColumns')->push($this->getTextValue($o));
        });

        return Registry::get('indexColumns')->toArray();
    }

    public function destroy()
    {
        parent::destroy();
        Registry::remove('indexColumns');
        Registry::remove('colCount');
        Registry::remove('isUnique');
    }
}
