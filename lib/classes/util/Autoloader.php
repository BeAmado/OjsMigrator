<?php

namespace BeAmado\OjsMigrator\Util;

class Autoloader
{
    /**
     * Forms the fullpath of the file where the specified class might be
     *
     * @param string $classname
     * @param array $args
     * @return string
     */
    protected function formFullpath($classname, $args = array())
    {
        $full = \BeAmado\OjsMigrator\LIB_DIR;
        
        foreach ($args as $dir) {
            $full .= '/' . $dir;
        }

        $full .= '/' . $classname . '.php';
        return $full;
    }

    /**
     * Tries to include the specified file
     * 
     * @param string $fullpath
     * @return boolean
     */
    protected function includeElement($fullpath)
    {
        if (is_file($fullpath)) {
            include_once($fullpath);
            return true;
        }

        return false;
    }

    /**
     * Loads the specified class
     * 
     * @param string $className
     * @param array $parts
     * @return boolean
     */
    protected function loadClass($name, $parts = array())
    {
        if($this->includeElement(
            $this->formFullpath(
                $name, 
                array('classes', 'core')
            )
        )) return true;

        if (\count($parts) < 1) return false;

        \array_splice($parts, 0, 0, array('classes')); //puts classes in the beginning of the array 

        return $this->includeElement(
            $this->formFullpath($name, \array_map('strtolower', $parts))
        );
    }

    /**
     * Loads the specified interface
     * 
     * @param string $name
     * @return boolean
     */
    protected function loadInterface($name)
    {
        return $this->includeElement(
            $this->formFullpath(
                $name, 
                array('interfaces')
            )
        );
    }

    /**
     * Loads the specified trait
     * 
     * @param string $name
     * @return boolean
     */
    protected function loadTrait($name)
    {
        return $this->includeElement(
            $this->formFullpath(
                $name, 
                array('traits')
            )
        );
    }

    /**
     * Checks if the specified namespace is an interface 
     *
     * @param string $namespacedStr
     * @return boolean
     */
    protected function nameIsInterface($namespacedStr)
    {
        return in_array(
            'interface',
            explode('/', strtolower($namespacedStr))
        ) || in_array(
            'interfaces',
            explode('/', strtolower($namespacedStr))
        );
    }

    /**
     * Checks if the specified namespace is a trait
     *
     * @param string $namespacedStr
     * @return boolean
     */
    protected function nameIsTrait($namespacedStr)
    {
        return in_array(
            'trait',
            explode('/', strtolower($namespacedStr))
        ) || in_array(
            'traits',
            explode('/', strtolower($namespacedStr))
        );
    }

    /**
     * Loads the specified class
     *
     * @param string $namespacedClass
     * @return boolean
     */
    public function autoload($str)
    {
        $parts = \explode('\\', $str);

        // $parts[0] must be BeAmado
        // $parts[1] must be OjsMigrator
        if (
            count($parts) <  3 ||
            strtolower($parts[0]) !== 'beamado' ||
            strtolower($parts[1]) !== 'ojsmigrator'
        ) return false;

        //the last part must be the name of the class
        $classname = $parts[\count($parts) - 1];

        if ($this->nameIsInterface($str) && $this->loadInterface($classname)) return true;

        if ($this->nameIsTrait($str) && $this->loadTrait($classname)) return true;

         // the array without the first 2 elements and the last one   
         \array_splice($parts, 0, 2);
         \array_splice($parts, -1);

        return $this->loadClass($classname, $parts);
    }

    /**
     * Registers the loadClass as an autoload function 
     * @return boolean
     */
    public function registerAutoload()
    {
        return spl_autoload_register(array($this, 'autoload'));
    }

}
