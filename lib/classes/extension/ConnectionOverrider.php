<?php

namespace BeAmado\OjsMigrator\Extension;
use \BeAmado\OjsMigrator\Registry;

class ConnectionOverrider
{
    protected function askForAndGetConnectionSettings()
    {
        return \array_map(function($message) {
            return Registry::get('IoManager')->getUserInput($message);
        }, array(
            'driver' => 'Enter the database driver: ',
            'host' => 'Enter the database host: ',
            'username' => 'Enter the database user: ',
            'password' => 'Enter the database password: ',
            'name' => 'Enter the database name: ',
        ));
    }

    public function setConnection()
    {
        Registry::set(
            'connection',
            Registry::get('ConnectionManager')->createConnection(
                $this->askForAndGetConnectionSettings()
            )
        );
    }
}
