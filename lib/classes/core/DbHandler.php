<?php

namespace BeAmado\OjsMigrator;

class DbHandler
{
    /**
     * @var PDO
     */
    private $conn;

    /**
     * Creates a connection to MySQL
     *
     * @param array $connData
     */
    protected function createMySqlConnection($connData = array())
    {
        $host = $connData['host'];
        $db = $connData['db'];
        $user = $connData['user'];
        $pass = $connData['pass'];
        return new \PDO("mysql:host=$host;dbname=$db", $user, $pass);
    }

    /**
     * 
     * @param array $connData
     */
    public function __construct($args = array())
    {
        
    }
}
