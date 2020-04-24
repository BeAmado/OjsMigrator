<?php

namespace BeAmado\OjsMigrator\Util;
use \BeAmado\OjsMigrator\Registry;
use \BeAmado\OjsMigrator\Maestro;

class ConfigHandler
{
    /**
     * @var \BeAmado\OjsMigrator\MyObject
     */
    private $configurations;

    protected function findConfigFile()
    {
        if (Registry::hasKey('configFile')) {
            $this->setConfigFile(Registry::get('configFile'));
            return;
        }

        foreach (
            Registry::get('FileSystemManager')->listdir(
                Registry::get('OjsDir')
            ) as $filename
        ) {
            if (\basename($filename) === 'config.inc.php')
                return $filename;
        }
    }

    public function __construct($filename = null)
    {
        $this->configurations = Registry::get('MemoryManager')->create(array(
            'config_file' => null,
            'files_dir' => null,
            'driver' => null,
            'host' => null,
            'username' => null,
            'password' => null,
            'name' => null,
        ));

        $this->setConfigFile($filename ?: $this->findConfigFile());
        $this->setConfigurations();
    }

    /**
     * Sets the location of the config.in.php file
     *
     * @param string $filename
     * @return void
     */
    public function setConfigFile($filename)
    {
        if (!Registry::get('FileSystemManager')->fileExists($filename))
            throw new \Exception('The configuration file "' . $filename 
                . '" does not exist');

        $this->configurations->set('config_file', $filename);
    }

    protected function filesDirIsRelative()
    {
        return \substr(
            $this->getFilesDir(),
            0,
            1
        ) !== \BeAmado\OjsMigrator\DIR_SEPARATOR;
    }

    protected function setFilesDirWithAbsolutePath()
    {
        if (!$this->filesDirIsRelative())
            return;

        $this->getConfigurations()->set(
            'files_dir',
            Registry::get('FileSystemManager')->formPath(array(
                \BeAmado\OjsMigrator\Maestro::getOjsDir(),
                $this->getFilesDir(),
            ))
        );
    }

    protected function getConfiguration($name)
    {
        if ($this->getConfigurations()->hasAttribute($name))
            return $this->getConfigurations()->get($name)->getValue();
    }

    protected function getConfigSettings($fields = array())
    {
        return \array_combine(
            $fields,
            \array_map(function($field) {
                return $this->getConfiguration($field);
            }, $fields)
        );
    }

    public function getConnectionSettings()
    {
        return $this->getConfigSettings(array(
            'driver',
            'host',
            'username',
            'password',
            'name',
        ));
    }

    public function getFilesDir()
    {
        return $this->getConfigurations()
                    ->get('files_dir')
                    ->getValue();
    }

    protected function getConfigFile()
    {
        return $this->getConfigurations()
                    ->get('config_file')
                    ->getValue();
    }

    protected function getConfigContents()
    {
        return Registry::get('FileSystemManager')->fileExists(
            $this->getConfigFile()
        ) ? \file($this->getConfigFile()) : array();
    }

    protected function getConfigData($str)
    {
        return \array_map(function($item) {
            return \str_replace(array('"', "'"), '', \trim($item));
        }, \explode('=', $str));
    }

    protected function setConfigData($configData, $ignoreComments = true)
    {
        if (
            !\is_array($configData) ||
            \count($configData) < 2 ||
            !\is_string($configData[0]) ||
            !\is_string($configData[1]) || 
            ($ignoreComments && substr($configData[1], 0, 1) === ';')
        )
            return;


        if ($this->getConfigurations()->hasAttribute($configData[0]))
            $this->getConfigurations()->set(
                $configData[0],
                $configData[1]
            );
    }

    protected function getConfigurations()
    {
        return $this->configurations;
    }

    protected function setConfigurations()
    {
        foreach ($this->getConfigContents() as $line) {
            if ($this->getConfigurations()->forEachValue(function($config) {
                return !\is_null($config->getValue());
            }))
                break;

            $this->setConfigData($this->getConfigData($line));
        }
        unset($line);
    }
}
