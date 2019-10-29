<?php

namespace BeAmado\OjsMigrator\Db;
use \BeAmado\OjsMigrator\Registry;

abstract class AbstractDefinitionHandler
{
    /**
     * Checks whether or not the given object has the specified name.
     *
     * @param \BeAmado\OjsMigrator\MyObject | array $o
     * @param string $name
     * @return boolean
     */
    protected function nameIs($o, $name)
    {
        if (\is_array($o))
            return $this->nameIs(
                Registry::get('MemoryManager')->create($o), 
                $name
            );

        if (
            !\is_a($o, \BeAmado\OjsMigrator\MyObject::class) ||
            !$o->hasAttribute('name')
        ) {
            return false;
        }

        return \strtolower($o->get('name')->getValue()) === \strtolower($name);
    }

    /**
     * Gets the specified attribute which must be present in the attributes 
     * array of the given object.
     *
     * @param \BeAmado\OjsMigrator\MyObject | array $obj
     * @param string $attr
     * @return string
     */
    protected function getAttribute($obj, $attr)
    {
        if (\is_array($obj))
            return $this->getAttribute(
                Registry::get('MemoryManager')->create($obj),
                $attr
            );

        if (
            !\is_a($obj, \BeAmado\OjsMigrator\MyObject::class) ||
            !$obj->hasAttribute('attributes') ||
            !\is_a(
                $obj->get('attributes'), 
                \BeAmado\OjsMigrator\MyObject::class
            )
        ) {
            return;
        }

        if ($obj->get('attributes')->hasAttribute($attr))
            return $obj->get('attributes')->get($attr)->getValue();

        return '';
    }

    /**
     * Gets the text value of an element.
     *
     * @param \BeAmado\OjsMigrator\MyObject | array $obj
     * @return string
     */
    protected function getTextValue($obj)
    {
        if (\is_array($obj))
            return $this->getTextValue(
                Registry::get('MemoryManager')->create($obj), 
                $name
            );

        if (
            !\is_a($obj, \BeAmado\OjsMigrator\MyObject::class) ||
            !$obj->hasAttribute('text')
        ) {
            return;
        }

        return $obj->get('text')->getValue();
    }

    /**
     * Checks if the given object has the specified child
     *
     * @param \BeAmado\OjsMigrator\MyObject | array $obj
     * @param string $name
     * @return boolean
     */
    protected function hasChild($obj, $name)
    {
        if (\is_array($obj))
            return $this->hasChild(
                Registry::get('MemoryManager')->create($obj), 
                $name
            );

        Registry::remove('hasChild');
        Registry::set('hasChild', false);
        Registry::remove('name');
        Registry::set('name', $name);
        $obj->get('children')->forEachValue(function($child) {
            if ($this->nameIs($child, Registry::get('name')))
                Registry::set('hasChild', true);
        });
        Registry::remove('name');
        return Registry::get('hasChild');
    }

    public function destroy()
    {
        Registry::remove('hasChild');
        Registry::remove('name');
    }
}
