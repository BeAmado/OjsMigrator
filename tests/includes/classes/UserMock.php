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
        /** @var $filename string */
        $filename = null;
        switch(\strtolower($name)) {
            case 'batman':
                $filename = $this->formFilename('wayne');
                break;
        }
    }
}
