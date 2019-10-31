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
            !\method_exists($tableDefinition, 'getTableName')
        ) {
            return;
        }

        return 'SELECT '
          . \implode(', ', $tableDefinition->getPrimaryKeys())
          . ' FROM ' . $tableDefinition->getTableName()
          . ' ORDER BY ' . \implode(', ', $tableDefinition->getPrimaryKeys())
          . ' DESC LIMIT 1';
    }

    protected function generateParameters() {}

    protected function autoIncrement()
    {
        return 'AUTO_INCREMENT';
    }

    /**
     * Generates a query to create a table as specified in the given 
     * TableDefinition.
     *
     * @param \BeAmado\OjsMigrator\Db\TableDefinition $td
     * @return string
     */
    public function generateQueryCreateTable($td)
    {
        return 'CREATE TABLE ' . $td->toString();
    }

    /**
     * Generates the name of the parameter. 
     * For example generateParameterName(
     *     'insert', 
     *     'user_settings', 
     *     'setting_value'
     * ) -> would return ':insertUsersSettings_settingValue
     *
     * @param string $op
     * @param string $tableName
     * @param string $columnName
     * @return string
     */
    protected function generateParameterName($op, $tableName, $columnName)
    {
        return ':' . $op
        . Registry::get('CaseHandler')->transformCaseTo(
            'PascalCase',
            $tableName
          )
        . '_'
        . Registry::get('CaseHandler')->transformCaseTo(
            'camelCase',
            $columnName
        );
    }

    /**
     * Generates an array with the parameter names for a statement
     *
     * @param string $op
     * @param string $tableName
     * @param array $columns
     * @return array
     */
    public function generateParameterNames($op, $tableName, $columns)
    {
        $parameters = array();

        foreach ($columns as $column) {
            $parameters[$column] = $this->generateParameterName(
                $op,
                $tableName,
                $column
            );
        }

        return $parameters;
    }

    /**
     * Generate the parameters for the insert query
     *
     * @param \BeAmado\OjsMigrator\Db\TableDefinition $td
     * @return array
     */
    protected function generateParametersInsert($td)
    {
        $columns = array();

        foreach($td->getColumnNames() as $column) {
            if (!$td->getColumn($column)->isAutoIncrement())
                $columns[] = $column;
        }
        unset($column);

        return $this->generateParameterNames(
            'insert',
            $td->getTableName(),
            $columns
        );
    }

    protected function generateWhere($table, $where, $op = null)
    {
        if (empty($where))
            return '';

        if ($op == null) {
            $op = \str_replace(
                '.',
                '',
                '' . \array_sum(explode(' ', \microtime()))
            );
        }

        $str = ' WHERE ';

        foreach (
            $this->generateParameterNames($op, $table, $where)
            as $column => $param
        ) {
            $str .= $column . ' = ' . $param;
            if (!Registry::get('ArrayHandler')->isLast($column, $where))
                $str .= ' AND ';
        }

        return $str;
    }

    /**
     * Generates a query string for a insert statement of the specified table.
     *
     * @param \BeAmado\OjsMigrator\Db\TableDefinition $td
     * @return string
     */
    public function generateQueryInsert($td)
    {
        $parameters = $this->generateParametersInsert($td);

        return 'INSERT INTO ' . $td->getTableName()
            . ' (' 
            . \implode(', ', \array_keys($parameters))
            . ') VALUES ('
            . \implode(', ', \array_values($parameters))
            . ')';
    }

    /**
     * Generates a query string for a statement to update a specified table.
     *
     * @param \BeAmado\OjsMigrator\Db\TableDefinition $td
     * @param array $set
     * @param array $where
     * @return string
     */
    public function generateQueryUpdate($td, $set = array(), $where = array())
    {
        if (empty($where))
            $where = $td->getPrimaryKeys();

        if (empty($set)) {
            $set = \array_diff($td->getColumnNames(), $where);
        }

        $query = 'UPDATE ' . $td->getTableName() . ' SET ';

        foreach (
            $this->generateParameterNames('update', $td->getTableName(), $set) 
            as $column => $param
        ) {
            $query .= $column . ' = ' . $param . ', ';
        }

        $query = \substr($query, 0, -2) 
          . $this->generateWhere($td->getTableName(), $where, 'update');

        return $query;
    }

    /**
     * Generates a query string for a statement to delete records of a 
     * specified table.
     * 
     * @param \BeAmado\OjsMigrator\Db\TableDefinition $td
     * @param array $where
     * @return string
     */
    public function generateQueryDelete($td, $where = array())
    {
        if (empty($where))
            $where = $td->getPrimaryKeys();

        return 'DELETE FROM ' . $td->getTableName()
            . $this->generateWhere($td->getTableName(), $where, 'delete');
    }

    /**
     * Generate a query string for a statement to select records from a 
     * specified table.
     *
     * @param \BeAmado\OjsMigrator\Db\TableDefinition $td
     * @param array $where
     * @return string
     */
    public function generateQuerySelect($td, $where = array())
    {
        return 'SELECT ' . \implode(', ', $td->getColumnNames()) 
            . ' FROM ' . $td->getTableName()
            . $this->generateWhere($td->getTableName(), $where, 'select');
    }
}
