<?php

namespace BeAmado\OjsMigrator;

class EntityHandler
{
    public function getValidData($name, $data)
    {
        $tbDef = Registry::get('SchemaHandler')->getTableDefinition($name);
        $validData = new Entity(null, $tbDef->getTableName());

        $dataObj = Registry::get('MemoryManager')->create($data);

        foreach ($tbDef->getColumnNames() as $field) {
            $validData->set(
                $field,
                ($dataObj->attributeIsNull($field))
                    ? $tbDef->getColumn($field)->getDefaultValue()
                    : $dataObj->get($field)->getValue()
            );
        }

        Registry::get('MemoryManager')->destroy($dataObj);
        unset($dataObj);

        return $validData;
    }

    public function create($name, $data = null)
    {
        return $this->getValidData($name, $data);
    }
}
