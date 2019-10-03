<?php

namespace BeAmado\OjsMigrator;


class MyObject extends AbstractObject implements MyIterable
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
    public function __construct($vals = null)
    {
        if (\is_array($vals)) {
            $this->values = array();
            foreach ($vals as $key => $value) {
                $this->set($key, $value);
            }
            unset($key);
            unset($value);
        } else {
            parent::__construct($vals);
        }
    }

    /**
     * Gets the specified element of the array
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        if (\is_numeric($key) && $key < 0) {
            $key += count($this->values);
        }

        if (\array_key_exists($key, $this->values))
        {
            return $this->values[$key];
        }
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
        if (!\is_array($this->values)) {
            $this->values = array();
        }

        if (\is_a($value, MyObject::class)) {
            $this->values[$key] = $value;
        } else {
            $this->values[$key] = new MyObject($value);
        }
    }

    /**
     * Destroys the array singleton
     *
     * @return void
     */
    public function destroy()
    {
        //destroying the value property inherited from AbstractClass
        parent::destroy(); 

        if (isset($this->values)) {
            if (\is_array($this->values)) {
                $this->destroyArray($this->values);
            } else if (\is_object($this->values)) {
                $this->destroyObject($this->values);
            }
        }
        unset($this->values);
    }

    /**
     * Returns the values array
     *
     * @return array
     */
    public function listValues()
    {
        return $this->values;
    }

    /**
     * Iterates over the values
     *
     * @param callable $callback
     * @return void
     */
    public function forEachValue($callback)
    {
        if (!\is_array($this->values)) {
            return;
        }

        foreach ($this->values as $value) {
            $callback($value);
        }

        unset($value);
    }

    protected function getInnerValue($obj)
    {
        if ($obj->getValue() === null) {
            return $obj->toArray();
        } else {
            return $obj->getValue();
        }
    }

    /**
     * Translates the object into an associative array
     *
     * @return array
     */
    public function toArray()
    {
        $arr = array();

        /** @var $myObj \BeAmado\OjsMigrator\MyObject */
        foreach ($this->values as $key => $myObj) {
            if ($myObj->getValue() === null) {
                $arr[$key] = $myObj->toArray();
            } else if (\is_a(
                $myObj->getValue(),
                self::class
            )) {
                $arr[$key] = $this->getInnerValue($myObj);
            } else {
                $arr[$key] = $myObj->getValue();
            }
        }

        unset($key);
        unset($myObj);

        return $arr;
    }

    /**
     * Checks if the object has the specified attibute.
     *
     * @param string $name
     * @return boolean
     */
    public function hasAttribute($name)
    {
        return \array_key_exists($name, $this->values);
    }
}

