<?php

use BeAmado\OjsMigrator\MigrationManager;
use BeAmado\OjsMigrator\FunctionalTest;
use BeAmado\OjsMigrator\Registry;

use BeAmado\OjsMigrator\StubInterface;
use BeAmado\OjsMigrator\TestStub;

class MigrationManagerTest extends FunctionalTest implements StubInterface
{
    public function getStub()
    {
        return new class extends MigrationManager {
            use TestStub;
        };
    }

    public function testCanAccessTheMigrationManagerInTheRegistry()
    {
        $this->assertInstanceOf(
            MigrationManager::class,
            Registry::get('MigrationManager')
        );
    }

    public function testActionIsAValidMigrationOptionWithTypeString()
    {
        $this->assertTrue($this->getStub()->callMethod(
            'isValidMigrationOption',
            array(
                'name' => 'action',
                'value' => 'anything',
            )
        ));
    }

    public function testEntitiesToMigrateIsAValidMigrOptionWithTypeMyObject()
    {
        $this->assertTrue($this->getStub()->callMethod(
            'isValidMigrationOption',
            array(
                'name' => 'entitiesToMigrate',
                'value' => Registry::get('MemoryManager')->create(array(
                    'journals',
                    'issues',
                ))
            )
        ));
    }

    public function testCanPushJournalsAndIssuesAsEntitiesToMigrate()
    {
        $entities = array('journals', 'issues');
        foreach ($entities as $entity) {
            $this->getStub()->callMethod(
                'getMigrationOption',
                'entitiesToMigrate'
            )->push($entity);
        }

        $this->assertTrue(
            Registry::get('ArrayHandler')->equals(
                $entities,
                $this->getStub()->callMethod(
                    'getMigrationOption',
                    'entitiesToMigrate'
                )->toArray()
            )
        );
    }
}
