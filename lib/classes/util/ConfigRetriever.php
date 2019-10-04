<?php

namespace BeAmado\OjsMigrator\Util;
use \BeAmado\OjsMigrator\Util\FileSystemManager;
use \BeAmado\OjsMigrator\Util\FileHandler;

class ConfigRetriever
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

    protected function setConnectionSettings()
    {
        if(!$this->validateContent()) {
            return false;
        }
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
