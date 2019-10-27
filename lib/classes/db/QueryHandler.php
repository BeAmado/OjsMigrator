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
     * Generates a query to get the last inserted record in the table
     *
     * @param \BeAmado\OjsMigrator\Db\TableDefinition $tableDefinition
     * @return string
     */
    public function generateQueryGetLast($tableDefinition)
    {
        if (
            !\method_exists($tableDefinition, 'getPrimaryKeys') ||
            !\method_exists($tableDefinition, 'getName')
        ) {
            return;
        }

        return 'SELECT '
          . \implode(', ', $tableDefinition->getPrimaryKeys())
          . ' FROM ' . $tableDefinition->getName()
          . ' ORDER BY ' . \implode(', ', $tableDefinition->getPrimaryKeys())
          . ' DESC LIMIT 1';
    }

    protected function generateParameters() {}

    protected function autoIncrement()
    {
        return 'AUTO_INCREMENT';
    }

    public function generateQueryCreateTable($td)
    {
        if (
            !\method_exists($td, 'getPrimaryKeys') ||
            !\method_exists($td, 'getName')
        ) {
            return;
        }

        $query = 'CREATE TABLE ' . $td->getName() . ' (';
        
        foreach ($td->getColumnNames() as $column) {
            $query .= '`' . $column . '` ' . \strtoupper($td->getSqlType($column));

            if (!$td->isNullable($column))
                $query .= ' NOT NULL';

            if ($td->getDefaultValue($column) !== null) {
                $query .= ' DEFAULT ';
                if ($td->getDefaultValue($column) === '')
                    $query .= '""';
                else
                    $query .= $td->getDefaultValue($column);
            }

            if ($td->isAutoIncrement($column))
                $query .= $this->autoIncrement();

            $query .= ', ';
        }
        unset($column);

        $pks = $td->getPrimaryKeys();

        if (\count($pks))
            $query .= 'PRIMARY KEY(`' . \implode('`, `', $pks) . '`)';
        else if (\substr($query, -2) === ', ')
            $query = \substr($query, 0, -2);

        Registry::get('MemoryManager')->destroy($pks);
        unset($pks);

        $query .= ')';

        return $query;
    }

    public function generateQueryInsert($tableDefinition) {}

    public function generateQueryUpdate($tableDefinition) {}

    public function generateQueryDelete($tableDefinition) {}

    public function generateQuerySelect($tableDefinition) {}
}
