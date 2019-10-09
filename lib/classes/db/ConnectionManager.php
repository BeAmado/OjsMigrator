<?php

namespace BeAmado\OjsMigrator\Db;
use \BeAmado\OjsMigrator\Registry;

class ConnectionManager
{
    public function setConnection()
    {
        Registry::set(
            'connection',
            (new DbHandler())->createConnection(
                Registry::get('ConfigHandler')->getConnectionSettings()
            )
        );
    }

    public function getConnection()
    {
        return Registry::get('connection');
    }

    public function closeConnection()
    {
        Registry::remove('connection');
    }
}
