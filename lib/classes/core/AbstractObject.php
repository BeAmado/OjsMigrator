<?php

namespace BeAmado\OjsMigrator;


abstract class AbstractObject
{
    /**
     * @var mixed
     */
    protected $value;

    public function __construct($val = null)
    {
        if ($val !== null) {
            $this->value = $val;
        }

        unset($val);
    }

    /**
     * Returns the value of the singleton
     *
     * @return mixed
     */
    public function getValue()
    {
        return isset($this->value) ? $this->value : null;
    }

    protected function destroyArray(&$arr)
    {
        if (!\is_array($arr)) {
            unset($arr);
            return;
        }

        foreach (\array_keys($arr) as $key) {
            if (\is_array($arr[$key])) {
                $this->destroyArray($arr[$key]);
            }
            unset($arr[$key]);
        }

        unset($key);
        unset($arr);
    }

    protected function destroyObject($obj)
    {
        if (\method_exists($obj, 'destroy')) {
            $obj->destroy();
            unset($obj);
            return;
        }

        foreach (\array_keys(\get_object_vars($obj)) as $attr) {
            if (\is_array($obj->$attr)) {
                $this->destroyArray($obj->$attr);
            } else if (is_object($obj->$attr)){
                $this->destroyObject($obj->$attr);
            } else {
                unset($obj->$attr);
            }
        }

        unset($attr);
        unset($obj);
    }

    /**
     * Unsets the singleton value
     *
     * @return void
     */
    public function destroy()
    {
        if (\is_object($this->value)) {
            $this->destroyObject($this->value);
        }

        unset($this->value);
    }
}

