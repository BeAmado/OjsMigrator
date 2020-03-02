<?php

namespace BeAmado\OjsMigrator\Test;
use \BeAmado\OjsMigrator\Registry;

trait WorkWithOjsDir
{
    public function getOjsDir()
    {
        return $this->getSandboxDir() 
            . \BeAmado\OjsMigrator\DIR_SEPARATOR . 'ojs2'; 
    }

    public function getOjsPublicHtmlDir()
    {
        return $this->getOjsDir() 
            . \BeAmado\OjsMigrator\DIR_SEPARATOR . 'public_html';
    }

    public function getOjsFilesDir()
    {
        return $this->getOjsDir() 
            . \BeAmado\OjsMigrator\DIR_SEPARATOR . 'files';
    }

    public function getOjsConfigFile()
    {
        return $this->getOjsPublicHtmlDir() 
            . \BeAmado\OjsMigrator\DIR_SEPARATOR . 'config.inc.php';
    }

    public function getSandboxDir()
    {
        return Registry::get('FileSystemManager')->formPathFromBaseDir(
            array(
                'tests',
                '_data',
                'sandbox',
            )
        );
    }

    public function createSandbox()
    {
        $sandbox = $this->getSandboxDir();
        
        if (!Registry::get('FileSystemManager')->dirExists($sandbox))
            Registry::get('FileSystemManager')->createDir($sandbox);

        unset($sandbox);
    }

    public function untarOjsDir()
    {
        $ojsTar = Registry::get('FileSystemManager')->formPathFromBaseDir(
            array(
                'tests',
                '_data',
                'ojs2.tar.gz',
            )
        );

        Registry::get('Archivemanager')->tar(
            'xzf',
            $ojsTar,
            $this->getSandboxDir()
        );
    }

    public function ojsDirExists()
    {
        return Registry::get('FileSystemManager')->dirExists(
            $this->getOjsPublicHtmlDir()
        );
    }

    public function prepareStage()
    {
        if (!Registry::hasKey('OjsDir'))
            Registry::set('OjsDir', $this->getOjsPublicHtmlDir());

        if (!$this->ojsDirExists())
            $this->createSandbox();
            $this->untarOjsDir();
    }
}
