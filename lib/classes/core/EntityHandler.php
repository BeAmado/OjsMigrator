<?php

namespace BeAmado\OjsMigrator;

class EntityHandler
{
    public function getValidData($name, $data)
    {
        $dataObj = Registry::get('MemoryManager')->create($data);
        $validData = Registry::get('MemoryManager')->create();

        $tbDef = Registry::get('SchemaHandler')->getTableDefinition($name);
        foreach ($tbDef->getColumnNames() as $field) {
            $validData->set(
                $field
                ($dataObj->attributeIsNull($field))
                    ? $tbDef->getColumn($field)->getDefaultValue()
                    : $dataObj->get($field)->getValue()
            );
        }

        Registry::get('MemoryManager')->destroy($dataObj);
        unset($dataObj);

        return $validData;
    }

    public function create($name, $data)
    {
        return new Entity($name, $this->getValidData($name, $data));
    }
}
