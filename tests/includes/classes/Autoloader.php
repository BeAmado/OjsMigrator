<?php

namespace BeAmado\OjsMigrator\Test;
use \BeAmado\OjsMigrator\Registry;

class Autoloader extends \BeAmado\OjsMigrator\Util\Autoloader
{
    /**
     * @var string
     */
    private $baseDir;

    protected function setBaseDir($dir)
    {
        $this->baseDir = $dir;
    }

    protected function getBaseDir()
    {
        return $this->baseDir . '';
    }

    public function __construct()
    {
        $this->setBaseDir(
            Registry::get('FileSystemManager')->formPathFromBaseDir([
                'tests',
                'includes',
            ])
        );
    }

    protected function formFullpath($classname, $args = [])
    {
        return implode(\BeAmado\OjsMigrator\DIR_SEPARATOR, array_merge(
            [$this->getBaseDir()],
            $args,
            [($classname . '.php')]
        ));
    }

    public function autoload($str)
    {
        /*
        if (\in_array(
            'registry', 
            \array_map(
                'strtolower', 
                \explode('\\', $str)
            )
        ))
            return parent::autoload('BeAmado\OjsMigrator\Registry');
        */

        if (!\in_array(
            'test', 
            \array_map(
                'strtolower',
                \explode('\\', $str)
            )
        ))
            return false;
        
        if ($this->loadClass(
            \array_slice(\explode('\\', $str), -1)[0],
            []
        ))
            return true;

        return parent::autoload($str);
    }
}
