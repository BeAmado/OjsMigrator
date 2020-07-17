<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Db\Schema;

// interfaces
use BeAmado\OjsMigrator\Test\StubInterface;

// traits
use BeAmado\OjsMigrator\Test\TestStub;
use BeAmado\OjsMigrator\Test\WorkWithXmlSchema;

class SchemaTest extends TestCase implements StubInterface
{
    use WorkWithXmlSchema;

    public function getStub()
    {
        return new class extends Schema {
            use TestStub;
        };
    }

    public function testGetJournalsTableDefinition()
    {
        $schema = new Schema($this->schemaArray());

        $this->assertInstanceOf(
            \BeAmado\OjsMigrator\Db\TableDefinition::class,
            $schema->getDefinition('journals')
        );
    }
}