<?php

namespace BeAmado\OjsMigrator;

class ConfigPreprocessor
{
    /**
     * @var \BeAmado\OjsMigrator\MyObject
     */
    private $vars;

    protected function parseDbDriver($args)
    {
        if (
            !\array_key_exists('dbDriver', $args) ||
            !\in_array(
                $args['dbDriver'],
                Registry::get('ConnectionManager')->supportedDrivers()
            )
        )
            return 'sqlite';

        return $args['dbDriver'];
    }

    public function __construct($args = array())
    {
        $this->vars = Registry::get('MemoryManager')->create(array(
            'OjsScenarioHandler' => new OjsScenarioHandler(),
            'dbDriver' => $this->parseDbDriver($args),
        ));
    }

    public function destroy()
    {
        $this->vars->destroy();
        unset($this->vars);
    }

    protected function setFilesDir($str)
    {
        return str_replace(
            '[ojs2_dir]',
            $this->getOjsScenarioHandler()->getOjsDir(),
            $str
        ) . PHP_EOL;
    }

    protected function setDbName()
    {
        switch($this->getDbDriver()) {
            case 'sqlite':
                return 'name = ' . $this->getOjsScenarioHandler()->getOjsDir()
                    . DIR_SEPARATOR . 'tests_ojs.db' . PHP_EOL;
            case 'mysql':
                return 'name = tests_ojs' . PHP_EOL;
        }
    }

    protected function getDbDriver()
    {
        return $this->vars->get('dbDriver')->getValue();
        /*if (array_search('pdo_sqlite', get_loaded_extensions())) {
            return 'sqlite';
        } else if (array_search('pdo_mysql', get_loaded_extensions())) {
            return 'mysql';
        }*/
    }

    protected function setDbDriver()
    {
        if ($this->getDbDriver() === 'sqlite')
            return 'driver = sqlite' . PHP_EOL;
        
        if ($this->getDbDriver() === 'mysql')
            return 'driver = mysql' . PHP_EOL;
    }

    protected function isInTheLine($id, $str)
    {
        switch($id) {
            case 'files_dir':
                return substr($str, 0, 11) === 'files_dir =';
            case 'name':
                return substr($str, 0, 6) === 'name =';
            case 'driver':
                return substr($str, 0, 8) === 'driver =';
        }
        return false;
    }

    protected function getOjsScenarioHandler()
    {
        return $this->vars->get('OjsScenarioHandler')->getValue();
    }

    public function createConfigFile()
    {
        $this->vars->remove('config');
        $this->vars->set(
            'config',
            array()
        );

        foreach (
            \file(
                $this->getOjsScenarioHandler()
                     ->getOjsConfigTemplateFile()
            ) as $line
        ) {
            if ($this->isInTheLine('files_dir', $line))
                $this->vars->get('config')->push($this->setFilesDir($line));
            else if ($this->isInTheLine('name', $line))
                $this->vars->get('config')->push($this->setDbName());
            else if ($this->isInTheLine('driver', $line))
                $this->vars->get('config')->push($this->setDbDriver());
            else
                $this->vars->get('config')->push($line);
        }

        return Registry::get('FileHandler')->write(
            $this->getOjsScenarioHandler()->getOjsConfigFile(),
            $this->vars->get('config')->toArray()
        );
    }
}
