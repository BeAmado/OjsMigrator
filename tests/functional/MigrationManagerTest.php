<?php

use BeAmado\OjsMigrator\MigrationManager;
use BeAmado\OjsMigrator\Test\FunctionalTest;
use BeAmado\OjsMigrator\Registry;

// interfaces
use BeAmado\OjsMigrator\Test\StubInterface;

// traits
use BeAmado\OjsMigrator\Test\TestStub;

class MigrationManagerTest extends FunctionalTest implements StubInterface
{
    public function getStub()
    {
        return new class extends MigrationManager {
            use TestStub;
        };
    }

    public function testTheMigrationOptionsAreInitiallySet()
    {
        $options = Registry::get('MigrationManager')->getMigrationOptions();
        $this->assertSame(
            '1-1',
            implode('-', array(
                (int) $this->areEqual(
                    '',
                    $options->get('action')->getValue()
                ),
                (int) is_a(
                    $options->get('entitiesToMigrate'),
                    \BeAmado\OjsMigrator\MyObject::class
                )
            ))
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
            Registry::get('MigrationManager')->getMigrationOption(
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
