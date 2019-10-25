<?php

namespace BeAmado\OjsMigrator;

class ConfigPreprocessor
{
    /**
     * @var \BeAmado\OjsMigrator\MyObject
     */
    private $vars;

    public function __construct()
    {
        $this->vars = Registry::get('MemoryManager')->create(array(
            'OjsScenarioTester' => new OjsScenarioTester(),
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
            $this->getOjsScenarioTester()->getOjsDir(),
            $str
        ) . PHP_EOL;
    }

    protected function setDbName()
    {
        switch($this->getDbDriver()) {
            case 'sqlite':
                return 'name = ' . $this->getOjsScenarioTester()->getOjsDir()
                    . DIR_SEPARATOR . 'tests_ojs.db' . PHP_EOL;
            case 'mysql':
                return 'name = tests_ojs' . PHP_EOL;
        }
    }

    protected function getDbDriver()
    {
        if (array_search('pdo_sqlite', get_loaded_extensions())) {
            return 'sqlite';
        } else if (array_search('pdo_mysql', get_loaded_extensions())) {
            return 'mysql';
        }
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

    protected function getOjsScenarioTester()
    {
        return $this->vars->get('OjsScenarioTester')->getValue();
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
                $this->getOjsScenarioTester()
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

        unset($line);

        return Registry::get('FileHandler')->write(
            $this->getOjsScenarioTester()->getOjsConfigFile(),
            $this->vars->get('config')->toArray()
        );
    }
}
