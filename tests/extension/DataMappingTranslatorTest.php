<?php

use BeAmado\OjsMigrator\Extension\DataMappingTranslator;
use BeAmado\OjsMigrator\Test\FunctionalTest;
use BeAmado\OjsMigrator\Test\StubInterface;
use BeAmado\OjsMigrator\Test\TestStub;
use BeAmado\OjsMigrator\Test\UseData;
use BeAmado\OjsMigrator\Registry;

class DataMappingTranslatorTest extends FunctionalTest implements StubInterface
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

    public function testMapTheDataForAnEntity()
    {
        $this->getStub()->callMethod(
            'mapData',
            'sections'
        );

        $this->assertSame(
            '101-102-103-104',
            implode('-', [
                Registry::get('DataMapper')->getMapping('sections', 1),
                Registry::get('DataMapper')->getMapping('sections', 2),
                Registry::get('DataMapper')->getMapping('sections', 3),
                Registry::get('DataMapper')->getMapping('sections', 4),
            ])
        );
    }
}
