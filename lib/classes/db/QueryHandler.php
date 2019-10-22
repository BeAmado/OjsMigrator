<?php

namespace BeAmado\OjsMigrator\Db;
use \BeAmado\OjsMigrator\Registry;

class QueryHandler
{
    public function __construct()
    {
        if (!Registry::hasKey('QueriesDir'))
            Registry::set(
                'QueriesDir',
                Registry::get('FileSystemManager')->formPathFromBaseDir(array(
                    'includes',
                    'queries',
                ))
            );
    }

    /**
     * Gets the fullpath of the query file.
     *
     * @param string $name
     * @return string
     */
    protected function getFileLocation($name)
    {
        if (\strpos($name, '-'))
            $name = \explode('-', $name)[0];

        $filename = Registry::get('FileSystemManager')->formPath(array(
            Registry::get('QueriesDir'),
            $name . '.php',
        ));

        if (Registry::get('FileSystemManager')->fileExists($filename))
            return $filename;
    }

    /**
     * Gets the data of the query file.
     *
     * @param string $name
     * @return string
     */
    protected function retrieve($name)
    {
        $pieces = \explode('-', $name);
        $location = $this->getFileLocation($pieces[0]);

        if (!$location)
            return;

        return (include($location))[$pieces[1]][$pieces[2]];
    }

    /**
     * Gets the query string of the specified query.
     *
     * @param string $name
     * @return string
     */
    public function getQuery($name)
    {
        $data = $this->retrieve($name);
        if (\is_array($data) && \array_key_exists('query', $data))
            return $data['query'];
    }

    /**
     * Gets the parameters names of the specified query.
     *
     * @param string $name
     * @return array
     */
    public function getParameters($name)
    {
        $data = $this->retrieve($name);
        if (\is_array($data) && \array_key_exists('params', $data))
            return $data['params'];
    }

    /**
     * Creates a query to get the last inserted record in the table
     *
     * @param \BeAmado\OjsMigrator\Db\TableDefinition $tableDefinition
     * @return string
     */
    public function createQueryGetLast($tableDefinition)
    {
        return 'SELECT '
          . \implode(', ', $tableDefinition->getPrimaryKeys())
          . ' FROM ' . $tableDefinition->getName()
          . ' ORDER BY ' . \implode(', ', $tableDefinition->getPrimaryKeys())
          . ' DESC LIMIT 1';
    }
}