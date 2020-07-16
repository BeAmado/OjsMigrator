<?php

namespace BeAmado\OjsMigrator\Db;
use BeAmado\OjsMigrator\Registry;

class StatementHandler
{
    /**
     * Creates a statement using the query identified by the name.
     * 
     * @param string $query
     * @return \BeAmado\OjsMigrator\Db\MyStatement
     */
    public function create($query)
    {
        return new MyStatement($query);
    }

    /**
     * Forms an array with the fields to go in the set part of an insert query.
     *
     * @param string $name
     * @param \BeAmado\OjsMigrator\MyObject $args
     * @return mixed
     */
    protected function formFields($name, $args)
    {
        if (!\is_string($name))
            return;

        if (\is_array($args))
            return $this->formFields(
                $name,
                Registry::get('MemoryManager')->create($args)
            );

        if (
            $args === null || 
            !\is_a($args, \BeAmado\OjsMigrator\MyObject::class) ||
            !$args->hasAttribute($name)
        )
            return;

        return $args->get($name)->listKeys();
    }

    /**
     * Forms the query as specified by the operation (insert, select, update,
     * delete or getlast), the table's name and the constraints present in the
     * $args array.
     *
     * @param string $operation
     * @param string $tableName
     * @param \BeAmado\OjsMigrator\MyObject $args
     * @return string
     */
    protected function formQuery($operation, $tableName, $args = null)
    {
        if (!\is_string($operation) || !\is_string($tableName))
            return;

        if (\strtolower(\substr($operation, 0, 7)) === 'getlast') {
            return Registry::get('QueryHandler')->generateQueryGetLast(
                Registry::get('SchemaHandler')->getTableDefinition($tableName),
                ((int) \substr($operation, 7)) ?: 1
            );
        }

        if (\strtolower($operation) === 'insert')
            return Registry::get('QueryHandler')->generateQueryInsert(
                Registry::get('SchemaHandler')->getTableDefinition($tableName),
                (
                    \is_array($args) &&
                    \array_key_exists('dontAutoIncrement', $args) 
                ) ? true : false
            );

        return Registry::get('QueryHandler')->{
            'generateQuery' . \ucfirst(\strtolower($operation))
        }(
            Registry::get('SchemaHandler')->getTableDefinition($tableName),
            $this->formFields('where', $args),
            \strtolower($operation) === 'update' 
                ? $this->formFields('set', $args) 
                : null
        );
    }

    /**
     * Sets the statement specified by the name.
     *
     * @param string $name
     * @param mixed $args
     * @return void
     */
    public function setStatement($name, $args = array())
    {
        if (Registry::hasKey($name))
            Registry::remove($name);

        if (\is_a($args, \BeAmado\OjsMigrator\Db\MyStatement::class)) {
            Registry::set($name, $args);
            return;
        }

        $pieces = \explode(
            '_',
            Registry::get('CaseHandler')->transformCaseTo('snake', $name)
        );

        if (\count($pieces) < 2)
            return;

        Registry::set(
            $name, 
            $this->create(
                $this->formQuery(
                    $pieces[0],
                    \implode('_', \array_slice($pieces, 1)),
                    $args
                )
            )
        );
    }

    protected function generateStatement($name, $args)
    {
        $pieces = \explode(
            '_',
            Registry::get('CaseHandler')->transformCaseTo('snake', $name)
        );

        if (\count($pieces) < 2)
            return; // TODO treat it better

        return $this->create(
            $this->formQuery(
                $pieces[0],
                \implode('_', \array_slice($pieces, 1)),
                $args
            )
        );
    }

    /**
     * Gets the statement specified by the name.
     *
     * @param string $name
     * @param array $args
     * @return \BeAmado\OjsMigrator\Db\MyStatement
     */
    public function getStatement($name, $args = null)
    {
        if (\is_array($args) && \array_key_exists('dontAutoIncrement', $args))
            return $this->generateStatement($name, $args);

        if (!Registry::hasKey($name))
            $this->setStatement($name, $args);

        return Registry::get($name);
    }

    /**
     * Removes the specified statement from the Registry.
     *
     * @param string $name
     * @return boolean
     */
    public function removeStatement($name)
    {
        if (\is_a(
            Registry::get($name), 
            \BeAmado\OjsMigrator\Db\MyStatement::class
        ))
            return Registry::remove($name);
    }

    /**
     * Binds the parameters to the statement.
     *
     * @param \BeAmado\OjsMigrator\Db\MyStatement $statement
     * @param \BeAmado\OjsMigrator\MyObject $data
     * @return boolean
     */
    protected function bindParameters($statement, $data)
    {
        if (
            !\is_a($data, \BeAmado\OjsMigrator\MyObject::class) ||
            !\is_a($statement, \BeAmado\OjsMigrator\Db\MyStatement::class)
        )
            return;
    
        if ($data->hasAttribute('where') || $data->hasAttribute('set'))
            $data->set(
                'payload',
                Registry::get('ArrayHandler')->union(
                    $data->get('set'),
                    $data->get('where')
                )
            );

        return $statement->bindParams(
            Registry::get('QueryHandler')->getParametersFromQuery(
                $statement->getQuery()
            ),
            $data->hasAttribute('payload') ? $data->get('payload') : $data
        );
    }

    protected function smHr()
    {
        return Registry::get('SubmissionHandler');
    }

    protected function smFileHr()
    {
        return Registry::get('SubmissionFileHandler');
    }

    protected function hasToPreventAutoIncrement($data)
    {
        if (
            !$this->smHr()->isEntity($data) ||
            !\in_array($data->getTableName(), array(
                $this->smHr()->formTableName('files')
            ))
        )
            return false;

        // returns true if the file is mapped and lets the SubmissionFileHandler
        // set the mapped id in the object
        if ($data->getTableName() === $this->smHr()->formTableName('files'))
            return $this->smFileHr()->setMappedFileId($data);
    }

    /**
     * Gets the statement identified by the name and contrained by the "where"
     * and/or "set" data
     *
     * @param string $stmtName
     * @param \BeAmado\OjsMigrator\MyObject | array
     * @return \BeAmado\OjsMigrator\Db\MyStatement
     */
    protected function getProperStatement($stmtName, $data)
    {
        if ($data === null)
            return $this->getStatement($stmtName);

        if (\is_array($data))
            $data = Registry::get('MemoryManager')->create($data);
        
        if (!\is_a($data, \BeAmado\OjsMigrator\MyObject::class))
            return;

        $args = ($data->hasAttribute('where') || $data->hasAttribute('set'))
            ? array() 
            : null;

        if ($data->hasAttribute('where'))
            $args['where'] = $data->get('where')->toArray();

        if ($data->hasAttribute('set'))
            $args['set'] = $data->get('set')->toArray();

        if (empty($args) && $this->hasToPreventAutoIncrement($data))
            $args = array('dontAutoIncrement' => true);

        return $this->getStatement($stmtName, $args);
    }

    /**
     * Executes the statement identified by the name passing the data to be 
     * bound and a callback to be executed for each record returned by the 
     * database.
     *
     * @param \BeAmado\OjsMigrator\Db\MyStatement | string $stmt
     * @param \BeAmado\OjsMigrator\MyObject $data
     * @param callable $callback
     * @return boolean
     */
    public function execute($stmt, $data = null, $callback = null)
    {
        $showStmt = $stmt === 'insertArticleFiles' && $data->get('file_id')->getValue() == 32;
        if (\is_array($data))
            $data = Registry::get('MemoryManager')->create($data);

        if (
            $data !== null && 
            !\is_a($data, \BeAmado\OjsMigrator\MyObject::class)
        )
            return;
            //TODO: treat better
        
        try {
            /** @var $statement \BeAmado\OjsMigrator\Db\Statement */
            $statement = \is_a(
                $stmt, 
                \BeAmado\OjsMigrator\Db\MyStatement::class
            )
                ? $stmt
                : $this->getProperStatement($stmt, $data);
    
            if ($data !== null && !$this->bindParameters($statement, $data)) {
                Registry::get('MemoryManager')->destroy($data);
                return false;
            }

            Registry::get('MemoryManager')->destroy($data);
    
            if (!$statement->execute())
                return false;
    
            if (\is_callable($callback))
                return $statement->fetch($callback);
    
            return true;
        } catch (\PDOException $e) {
            echo "\n\n\nEXCEPTION: ";
            echo "\n\nMessage: " . $e->getMessage() . "\n\n";
        }
    }
}
