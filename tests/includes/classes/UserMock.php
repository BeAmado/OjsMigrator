<?php

namespace BeAmado\OjsMigrator;

class UserMock
{
    protected function getUsersDir()
    {
        return Registry::get('FileSystemManager')->formPathFromBaseDir(array(
            'tests', '_data', 'users',
        ));
    }

    protected function formFilename($name)
    {
        return $this->getUsersDir()
            . \BeAmado\OjsMigrator\DIR_SEPARATOR 
            . \strtolower($name) . '.php';
    }

    public function getUser($name)
    {
        $filename = $this->formFilename(\strtr(
            \strtolower($name),
            array(
                'batman' => 'wayne',
                'ironman' => 'stark',
                'hulk' => 'banner',
                'hawkeye' => 'barton',
                'greenlantern' => 'jordan',
            )
        ));

        if (Registry::get('FileSystemManager')->fileExists($filename))
            return Registry::get('MemoryManager')->create(include($filename));
    }
}
