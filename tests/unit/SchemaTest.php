<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Db\Schema;
use BeAmado\OjsMigrator\StubInterface;
use BeAmado\OjsMigrator\TestStub;
use BeAmado\OjsMigrator\WorkWithXmlSchema;

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
