<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Extension\DataMappingTranslator;
use BeAmado\OjsMigrator\Test\StubInterface;
use BeAmado\OjsMigrator\Test\TestStub;
use BeAmado\OjsMigrator\Test\UseData;

class DataMappingTranslatorTest extends TestCase implements StubInterface
{
    use UseData;
    public function getStub()
    {
        return new class extends DataMappingTranslator {
            use TestStub;
        };
    }

    protected function mappingsFilename()
    {
        return $this->getFromDataDir('mappings.xml');
    }

    public function testCanReadTheXmlMappings()
    {
        $dataMapping = $this->getStub()->callMethod(
            'readXmlDataMapping',
            $this->mappingsFilename()
        );

        $this->assertTrue(false);
    }
}
