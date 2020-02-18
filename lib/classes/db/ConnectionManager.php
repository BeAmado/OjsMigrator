<?php

namespace BeAmado\OjsMigrator\Db;
use \BeAmado\OjsMigrator\Registry;

class ConnectionManager
{
    public function supportedDrivers()
    {
        return array(
            'mysql',
            'sqlite',
        );
    }

    public function setConnection()
    {
        Registry::set(
            'connection',
            $this->createConnection(
                Registry::get('ConfigHandler')->getConnectionSettings()
            )
        );

        Registry::get('connection')->setAttribute(
            \PDO::ATTR_ERRMODE, 
            \PDO::ERRMODE_EXCEPTION
        );
    }

    public function getDbDriver()
    {
        $settings = Registry::get('ConfigHandler')->getConnectionSettings();

        if (!\array_key_exists('driver', $settings))
            return 'sqlite';

        return $settings['driver'];
    }

    protected function filterDbName($name)
    {
        if (\strpos($name, \BeAmado\OjsMigrator\DIR_SEPARATOR) !== false)
            $name = \basename($name);

        if (\strtolower(\substr($name, -3)) === '.db')
            $name = substr($name, 0, -3);

        return $name;
    }

    /**
     * Creates a connection to MySQL
     *
     * @param array $connData
     */
    protected function createMySqlConnection($connData = array())
    {
        $host = $connData['host'];
        $db = $this->filterDbName($connData['name']);
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
    public function createConnection($args = array())
    {
        if (!\array_key_exists('driver', $args)) {
            return;
        }

        switch(\strtolower($args['driver'])) {
            case 'mysql':
                return $this->createMySqlConnection($args);

            case 'sqlite':
                return $this->createSqliteConnection($args['name']);
        }
    }

    public function getConnection()
    {
        if (!Registry::hasKey('connection'))
            $this->setConnection();

        return Registry::get('connection');
    }

    public function closeConnection()
    {
        Registry::remove('connection');
    }

    public function inTransaction()
    {
        return $this->getConnection()->inTransaction();
    }

    public function beginTransaction()
    {
        if (!$this->inTransaction())
            return $this->getConnection()->beginTransaction();
    }

    public function rollbackTransaction()
    {
        if ($this->inTransaction())
            return $this->getConnection()->rollback();
    }

    public function commitTransaction()
    {
        if ($this->inTransaction())
            return $this->getConnection()->commit();
    }
}
