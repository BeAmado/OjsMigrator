<?php

namespace BeAmado\OjsMigrator\Db\Sqlite;
use \BeAmado\OjsMigrator\Db\DAO;
use \BeAmado\OjsMigrator\Registry;
use \BeAmado\OjsMigrator\Entity\Entity;

class SqliteDAO extends DAO
{
    protected function hasToManuallyIncrement()
    {
        return \in_array(
            Registry::get('ConnectionManager')->getDbDriver(), 
            array(
                'sqlite',
            )
        ) && \in_array(
            $this->getTableName(), 
            array(
                Registry::get('SubmissionHandler')->formTableName('files'),
            )
        );
    }

    protected function formIdFieldToIncrement()
    {
        switch($this->getTableName()) {
            case Registry::get('SubmissionHandler')->formTableName('files'):
                return 'file_id';
        }

        return '';
    }

    protected function lastIdStatementName()
    {
        return 'getLastIdFor' . Registry::get('CaseHandler')->transformCaseTo(
            'Pascal',
            $this->getTableName()
        );
    }

    protected function createStatementGetLastId()
    {
        Registry::set(
            $this->lastIdStatementName(),
            Registry::get('StatementHandler')->create(
                'SELECT ' . $this->formIdFieldToIncrement() . ' AS id'
                    . ' FROM ' . $this->getTableName()
                    . ' ORDER BY ' . $this->formIdFieldToIncrement()
                    . ' DESC LIMIT 1'
            )
        );
    }

    protected function getStatementGetLastId()
    {
        if (!Registry::hasKey($this->lastIdStatementName()))
            $this->createStatementGetLastId();
        
        return Registry::get($this->lastIdStatementName());
    }

    public function formManualIncrementedId()
    {
        Registry::remove('__lastId__');
        Registry::get('StatementHandler')->execute(
            $this->getStatementGetLastId(),
            null,
            function($res) {
                Registry::set('__lastId__', $res['id']);
            }
        );

        if (\is_numeric(Registry::get('__lastId__')))
            return Registry::get('__lastId__') + 1;

        return 1;
    }

    protected function manuallyIncrementId($entity)
    {
//        echo "\n\n\nIncrementing the id: \n";
//        echo "\nEntity Before: "; var_dump(Registry::get('entityToInsert'));
        if (\is_array($entity))
            $entity = Registry::get('MemoryManager')->create($entity);

        $entity->set(
            $this->formIdFieldToIncrement(),
            $this->formManualIncrementedId()
        );

        return $entity;

//        echo "\n\nEntity After: ";var_dump(Registry::get('entityToInsert'));
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
        return parent::create(
            $this->hasToManuallyIncrement() 
                ? $this->manuallyIncrementId($entity)
                : $entity, 
            $options
        );
        /*
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
        */
    }
}
