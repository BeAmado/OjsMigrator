<?php

namespace BeAmado\OjsMigrator\Db;

class DbHandler
{
    /**
     * Creates a connection to MySQL
     *
     * @param array $connData
     */
    protected function createMySqlConnection($connData = array())
    {
        $host = $connData['host'];
        $db = $connData['name'];
        $user = $connData['username'];
        $pass = $connData['password'];
        return new \PDO("mysql:host=$host;dbname=$db", $user, $pass);
    }
    
    /**
     * Creates a connectin to a Sqlite database
     *
     * @param string $db
     */
    protected function createSqliteConnection($db)
    {
        return new \PDO('sqlite:' . $db);
    }

    /**
     * Creates a database connection using the specified driver
     *
     * @param string $driver
     * @param array $args
     * @return \PDO
     */
    public function createConnection($driver, $args = array())
    {
        switch(\strtolower($driver)) {
            case 'mysql':
                return $this->createMySqlConnection($args);

            case 'sqlite':
                return $this->createSqliteConnection($args);
        }
    }
}
