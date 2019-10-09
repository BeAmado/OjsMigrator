<?php

namespace BeAmado\OjsMigrator\Util;
use \BeAmado\OjsMigrator\Util\FileSystemManager;
use \BeAmado\OjsMigrator\Util\FileHandler;

class ConfigHandler
{
    /**
     * @var string
     */
    private $configFile;

    /**
     * @var array
     */
    private $configContent;

    /**
     * @var array
     */
    private $connectionSettings;

    /**
     * @var string
     */
    private $filesDir;

    public function __construct($filename)
    {
        $this->setConfigFile($filename);
        $this->setFilesDir();
        $this->setConnectionSettings();
    }

    /**
     * Sets the location of the config.in.php file
     *
     * @param string $filename
     * @return boolean
     */
    public function setConfigFile($filename)
    {
        if (!(new FileSystemManager())->fileExists($filename)) {
            return false;
            // TODO raise an Exception
        }

        $this->configFile = $filename;
        return true;
    }

    /**
     * Sets the content that is inside the config.inc.php file
     *
     * @return boolean
     */
    protected function setContent()
    {
        if (!(new FileSystemManager())->fileExists($this->configFile)) {
            return false;
            // TODO treat better, maybe raise an Exception
        }

        $this->configContent = \file($this->configFile);

        return \is_array($this->configContent);
    }

    protected function validateContent()
    {
        if (
            !\is_array($this->configContent) &&
            !$this->setContent()
        ) {
            return false;
            // TODO treat better, maybe raise an Exception
        }

        return true;
    }
    
    /**
     * Tests if the given string is a configuration of the connection setting 
     * identified by the specified name. If it is, include its value to the 
     * connectionSettings array.
     *
     * @param string $name
     * @param string $str
     * @return boolean
     */
    protected function testForAndAddConnectionSetting($name, $str)
    {
        if (\substr($str, 0, (strlen($name) + 2)) === $name . ' =') {
            $this->connectionSettings[$name] = \trim(\substr(
                $str, 
                strlen($name) + 2
            ));

            return true;
        }

        return false;
    }

    protected function setConnectionSettings()
    {
        if(!$this->validateContent()) {
            return false;
        }

        $this->connectionSettings = array();

        foreach ($this->configContent as $line) {
            $this->testForAndAddConnectionSetting('driver', $line) ||
            $this->testForAndAddConnectionSetting('host', $line) ||
            $this->testForAndAddConnectionSetting('username', $line) ||
            $this->testForAndAddConnectionSetting('password', $line) ||
            $this->testForAndAddConnectionSetting('name', $line); 
        }

        unset($line);
    }

    protected function setFilesDir()
    {
        if(!$this->validateContent()) {
            return false;
        }

        foreach ($this->configContent as $line) {
            if (\substr($line, 0, 11) === 'files_dir =') {
                $this->filesDir = \substr($line, 11); // from the 11th chararacter forth
            }
        }

        unset($line);

        if (\substr($this->filesDir, -1) == PHP_EOL) {
            $this->filesDir = \substr($this->filesDir, 0, -1);
        }

        $this->filesDir = \trim($this->filesDir);
    }

    public function getConnectionSettings()
    {
        return $this->connectionSettings;
    }

    public function getFilesDir()
    {
        return $this->filesDir;
    }
}