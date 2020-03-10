<?php

namespace BeAmado\OjsMigrator\Db;
use BeAmado\OjsMigrator\Registry;
use BeAmado\OjsMigrator\Entity\Entity;

class DAO
{
    /**
     * @var string
     *
     * The name of the table this DAO works upon
     */
    protected $tableName;

    /**
     *
     * @param string $name - The name of the table
     */
    public function __construct($name)
    {
        if (!\is_string($name))
            throw new \Exception('The DAO name must be a string');
            // TODO: treat better
        
        $this->tableName = $name;
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    protected function formStatementName($operation)
    {
        return \strtolower($operation) 
          . Registry::get('CaseHandler')->transformCaseTo(
                'PascalCase', 
                $this->getTableName()
            );
    }

    protected function getQuery($operation)
    {
        return Registry::get('StatementHandler')->getStatement(
            $this->formStatementName($operation)
        )->getQuery();
    }

    protected function getFieldsFromConditions($conditions)
    {
        return Registry::get('ArrayHandler')->union(
            \array_key_exists('where', $conditions) 
                ? \array_keys($conditions['where'])
                : array(),
            \array_key_exists('set', $conditions) 
                ? \array_keys($conditions['set'])
                : array()
        );
    }

    protected function getQueryParameters($operation)
    {
        return Registry::get('QueryHandler')->getParametersFromQuery(
            $this->getQuery($operation)
        );
    }

    /**
     * Checks if the statement is what it is suposed to be.
     * 
     * @param string $operation
     * @param array $conditions
     * @return boolean
     */
    protected function statementOk($operation, $conditions)
    {
        if (!\in_array(
            \strtolower($operation),
            array('update', 'select', 'delete')
        ))
            return true;

        return Registry::get('ArrayHandler')->equals(
            $this->getFieldsFromConditions($conditions),
            \array_keys($this->getQueryParameters($operation))
        );
    }

    /**
     * Inserts the entity's data into the corresponding database table.
     *
     * @param \BeAmado\OjsMigrator\Entity\Entity | array $entity
     * @param boolean $commitOnSucess
     * @param boolean $rollbackOnError
     * @return \BeAmado\OjsMigrator\Entity\Entity
     */
    public function create(
        $entity, 
        $options = array(
            'commitOnSuccess' => false, 
            'rollbackOnError' => false,
        )
    ) {
        Registry::remove('entityToInsert');
        Registry::set(
            'entityToInsert',
            Registry::get('EntityHandler')->getValidData(
                $this->getTableName(),
                $entity
            )
        );

        Registry::get('StatementHandler')->execute(
            'insert' . Registry::get('CaseHandler')->transformCaseTo(
                'PascalCase',
                $this->getTableName()
            ),
            Registry::get('entityToInsert')->cloneInstance()
        );

        Registry::remove('insertedEntity');

        Registry::get('StatementHandler')->execute(
            'getlast10' . Registry::get('CaseHandler')->transformCaseTo(
                'PascalCase',
                $this->getTableName()
            ),
            null,
            function($res) {
                if (Registry::get('EntityHandler')->areEqual(
                    Registry::get('entityToInsert'),
                    $res
                )) {
                    Registry::set(
                        'insertedEntity', 
                        Registry::get('EntityHandler')->create(
                            $this->getTableName(),
                            $res
                        )
                    );
                    return;
                }

                return true;
            }
        );

        Registry::remove('entityToInsert');

        if (!\is_a(
            Registry::get('insertedEntity'), 
            \BeAmado\OjsMigrator\MyObject::class)
        ) {
            Registry::remove('insertedEntity');
            return;
        }

        return Registry::get('insertedEntity')->cloneInstance();
    }

    /**
     * Reads (SELECT) the corresponding database table data into an array of
     * entities.
     *
     * @param array $conditions
     * @return \BeAmado\OjsMigrator\MyObject
     */
    public function read($data = array())
    {
        Registry::remove('selectData');

        $conditions = array();

        if (\is_a($data, \BeAmado\OjsMigrator\MyObject::class)) {
            if ($data->hasAttribute('where'))
                $conditions = $data->toArray();
            else
                $conditions = array(
                    'where' => Registry::get('EntityHandler')->getPrimaryKeys(
                        $data,
                        $this->getTableName()
                    ),
                );
        } else if (
            \is_array($data) && 
            !\array_key_exists('where', $data) &&
            !empty($data)
        )
            $conditions = array('where' => $data);

        if (!$this->statementOk('select', $conditions))
            Registry::get('StatementHandler')->removeStatement(
                $this->formStatementName('select')
            );

        Registry::get('StatementHandler')->execute(
            $this->formStatementName('select'),
            empty($conditions) ? null : $conditions,
            function($res) {
                if (!Registry::hasKey('selectData'))
                    Registry::set(
                        'selectData',
                        Registry::get('MemoryManager')->create(array())
                    );

                Registry::get('selectData')->push(
                    Registry::get('EntityHandler')->create(
                        $this->getTableName(),
                        $res
                    )
                );

                return true; // returning true continues the iteration
            }
        );

        return Registry::get('selectData') !== null
            ? Registry::get('selectData')->cloneInstance()
            : null;
    }

    protected function getRowCount($operation)
    {
        return Registry::get('StatementHandler')->getStatement(
            $this->formStatementName($operation)
        )->rowCount();
    }

    /**
     * Updates the corresponding database table using the given entity's data.
     *
     * @param mixed $data
     * @param boolean $commitOnSuccess
     * @param boolean $rollbackOnError
     * @return integer
     */
    public function update(
        $data, 
        $options = array(
            'commitOnSuccess' => false,
            'rollbackOnError' => false,
        )
    ) {
        $validEntity = \is_a($data, Entity::class) &&
            $data->getTableName() === $this->getTableName();
        
        $validConditions = \is_array($data) &&
            \array_key_exists('set', $data) &&
            \array_key_exists('where', $data);

        if (!$validEntity && !$validConditions) {
            return;
        }

        if (!$this->statementOk(
            'update', 
            \is_a($data, Entity::class) ? array() : $data
        )) {
            Registry::get('StatementHandler')->removeStatement(
                $this->formStatementName('update')
            );
        }

        $updated = Registry::get('StatementHandler')->execute(
            $this->formStatementName('update'),
            $data
        );

        if (!$updated) {
            unset($updated);
            return false;
        }

        return $this->getRowCount('update');
    }

    /**
     * Deletes the data of the corresponding database table using the given
     * conditions.
     *
     * @param mixed $conditions
     * @param boolean $commitOnSuccess
     * @param boolean $rollbackOnError
     * @return mixed
     */
    public function delete(
        $conditions = array(),
        $options = array(
            'commitOnSuccess' => false,
            'rollbackOnError' => false,
        )
    ) {
        if (empty($conditions))
            return 0;

        if (
            \is_array($conditions) && 
            !\array_key_exists('where', $conditions) &&
            !empty($conditions)
        )
            $conditions = array('where' => $conditions);

        if (
            !\is_array($conditions) &&
            !\is_a($conditions, \BeAmado\OjsMigrator\MyObject::class)
        )
            return;

        if (!$this->statementOk('delete', $conditions))
            Registry::get('StatementHandler')->removeStatement(
                $this->formStatementName('delete')
            );

        $executed = Registry::get('StatementHandler')->execute(
            $this->formStatementName('delete'),
            (\is_array($conditions) && empty($conditions))
                ? null
                : $conditions
        );

        if (!$executed) {
            unset($executed);
            return false;
        }

        return $this->getRowCount('delete');
    }

    protected function formJsonFilename($entity)
    {
        return Registry::get('EntityHandler')->getEntityDataDir($entity)
            . \BeAmado\OjsMigrator\DIR_SEPARATOR            
            . $entity->getId()
            . '.json';
    }

    protected function prepareEntityDataDir()
    {
        if (!Registry::get('FileSystemManager')->dirExists(
            Registry::get('EntityHandler')->getEntityDataDir(
                $this->getTableName()
            )
        ))
            Registry::get('FileSystemManager')->createDir(
                Registry::get('EntityHandler')->getEntityDataDir(
                    $this->getTableName()
                )
            );
    }

    public function dumpToJson($conditions)
    {
        if (
            \is_array($conditions) && 
            !\array_key_exists('where', $conditions) &&
            !empty($conditions)
        )
            $conditions = array('where' => $conditions);

        if (!$this->statementOk('select', $conditions))
            Registry::get('StatementHandler')->removeStatement(
                $this->formStatementName('select')
            );

        $this->prepareEntityDataDir();

        Registry::get('StatementHandler')->execute(
            $this->formStatementName('select'),
            $conditions,
            function($res) {
                $entity = Registry::get('EntityHandler')->create(
                    $this->getTableName(),
                    $res
                );
                Registry::get('JsonHandler')->dumpToFile(
                    $this->formJsonFilename($entity),
                    $entity
                );
                Registry::get('MemoryManager')->destroy($entity);
                return true;
            }
        );
    }
}
