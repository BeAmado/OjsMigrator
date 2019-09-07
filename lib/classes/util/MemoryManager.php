<?php

namespace BeAmado\OjsMigrator\Util;

class MemoryManager
{
    
    class Singleton
    {
        /**
         * @var mixed
         */
        private $value;

        public __construct($val)
        {
            $this->value = $val;
            unset($val);
        }

        /**
         * Returns the value of the singleton
         *
         * @return mixed
         */
        public function getValue()
        {
            return $value;
        }

        private function destroyArray($arr)
        {
        //  TODO
        }

        private function destroyObject($obj)
        {
            if (method_exists($obj, 'destroy')) {
                $obj->destroy();
                unset($obj);
                return;
            }

            foreach (get_object_vars($obj) as $attr) {
                if (is_array($obj->$attr)) {
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
            if (is_object($this->value)) {
                $this->destroyObject($this->value);
            }

            unset($this->value);
        }
    }

    class SingletonArray
    {
        /**
         * @var array
         */
        private $values;

        /**
         * Instantiates the singleton
         * 
         * @param array $values
         */
        public __construct($vals)
        {
            if (is_array($vals)) {
                $this->values = $vals;
            }
            unset($vals);
        }

        /**
         * Gets the specified element of the array
         *
         * @param string $key
         * @return mixed
         */
        public function get($key)
        {
            if (array_key_exists($key, $this->values))
            {
                return $this->values[$key];
            }
            unset($key);
        }
        
        /**
         * Sets the key in the array
         *
         * @param string $key
         * @param mixed $value
         * @return void
         */
        public function set($key, $value)
        {
            $this->values[$key] = $value;
            unset($key);
            unset($value);
        }

        private function destroyObject($obj)
        {

        }

        private function destroyArray($arr)
        {
            
        }

        /**
         * Destroys the array singleton
         *
         * @return void
         */
        public function destroy()
        {
            //foreach(array_keys($this->values) as $key) {
            //    unset($this->values[$key]);
            //}

            unset($key);
            unset($this->values);
        }
    }

    /**
     * Eliminates the variable from memory
     *
     * @param mixed $obj - The variable to be removed from memory
     * @return void
     */
    public function destroy($obj)
    {
        if (!is_array($obj) && !is_object($obj)) {
            unset($obj);
            return;
        }

        if (is_array($obj)) {
            foreach(array_keys($obj) as $key) {
                $this->destroy($obj[$key]);
            }
            unset($key);
        }

        if (method_exists($obj, 'destroy')) {
            $obj->destroy();
        } else {
            foreach(get_object_vars($obj) as $attr) {
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
    public function create($value)
    {
        if (is_array($value)) {
            return new SingletonArray($value);
        } else {
            return new Singleton($value);
        }
    }
}
