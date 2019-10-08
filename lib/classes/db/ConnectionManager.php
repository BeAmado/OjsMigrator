<?php

namespace BeAmado\OjsMigrator\Db;
use \BeAmado\OjsMigrator\Registry;

class ConnectionManager
{
    public function setConnection()
    {
        Registry::set(
            'connection',
            (new DbHandler())->createConnection()
        );
    }

    public function getConnection()
    {
        return Registry::get('connection');
    }
}
