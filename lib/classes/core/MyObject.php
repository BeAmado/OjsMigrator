<?php

namespace BeAmado\OjsMigrator;


class MyObject extends AbstractObject
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
	    $this->values = array();
        if (\is_array($vals)) {
            foreach ($vals as $key => $value) {
                $this->set($key, $value);
            }
            unset($key);
            unset($value);
        } else {
            parent::__construct($vals);
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
        if (\array_key_exists($key, $this->values))
        {
            return $this->values[$key];
        }
        unset($key);

        return 'bla';
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
        if (\is_a($value, MyObject::class)) {
            $this->values[$key] = $value;
        } else {
            $this->values[$key] = new MyObject($value);
        }

        unset($key);
        unset($value);
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
}

