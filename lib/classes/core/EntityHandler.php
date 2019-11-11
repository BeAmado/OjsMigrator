<?php

namespace BeAmado\OjsMigrator;

class EntityHandler
{
    protected function formDefaultValue($colDef)
    {
        if (!\is_a($colDef, \BeAmado\OjsMigrator\Db\ColumnDefinition::class))
            return;

        if ($colDef->getDefaultValue() !== null)
            return $colDef->getDefaultValue();

        if ($colDef->isNullable() || $colDef->isAutoIncrement())
            return null;

        switch(\strtolower($colDef->getSqlType())) {
            case 'date':
                return \date('Y-m-d');
            case 'datetime':
                return \date('Y-m-d H:i:s');
        }

        switch(\strtolower($colDef->getDataType())) {
            case 'string':
                return '';
            case 'integer':
            case 'float':
            case 'double':
                return 0;
        }

        return '0';
    }

    public function getValidData($name, $data)
    {
        $tbDef = Registry::get('SchemaHandler')->getTableDefinition($name);
        $validData = new Entity(null, $tbDef->getTableName());

        $dataObj = Registry::get('MemoryManager')->create($data);

        foreach ($tbDef->getColumnNames() as $field) {
            $validData->set(
                $field,
                ($dataObj->attributeIsNull($field))
                    ? $this->formDefaultValue($tbDef->getColumn($field))//$tbDef->getColumn($field)->getDefaultValue()
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
