<?php

namespace BeAmado\OjsMigrator\Util;

class MemoryManager
{
    
    /**
     * Eliminates the variable from memory
     *
     * @param mixed $obj - The variable to be removed from memory
     * @return void
     */
    public function destroy($obj)
    {
        if (!\is_array($obj) && !\is_object($obj)) {
            unset($obj);
            return;
        }

        if (\is_array($obj)) {
            foreach(\array_keys($obj) as $key) {
                $this->destroy($obj[$key]);
            }
            unset($key);
        }

        if (\method_exists($obj, 'destroy')) {
            $obj->destroy();
        } else {
            foreach(\get_object_vars($obj) as $attr) {
                unset($obj->$attr);
            }
            unset($attr);
        }

        unset($obj);
    }

    /**
     * Creates an object with the specified value
     *
     * @param mixed $value
     * @return StdClass
     */
    public function create($value = null)
    {
        return new \BeAmado\OjsMigrator\MyObject($value);
    }
}
