<?php

use BeAmado\OjsMigrator\Test\XmlDataMappingExtensionTest as ExtensionTest;
use BeAmado\OjsMigrator\Extension\XmlDataMappingProcessor;
use BeAmado\OjsMigrator\Registry;

use BeAmado\OjsMigrator\Test\TestStub;

class XmlDataMappingProcessorTest extends ExtensionTest 
{
    public function getStub()
    {
        return new class(
            self::mappingsFile(),
            self::mappingSandbox()
        ) extends XmlDataMappingProcessor {
            use TestStub;
        };
    }

    protected function processor()
    {
        return new XmlDataMappingProcessor(
            self::mappingsFile(),
            self::mappingSandbox()
        );
    }

    protected function sectionMappingFilename()
    {
        return $this->getStub()->callMethod(
            'formMappingFilename',
            'sections'
        );
    }

    public function testCanExtractTheSectionMappingInXml()
    {   
        $this->processor()->extractXmlMapping('sections');
        $this->assertSame(
            '1',
            implode('-', [
                (int) self::fsm()->fileExists($this->sectionMappingFilename()),
            ])
        );
    }

    /**
     * @depends testCanExtractTheSectionMappingInXml
     */
    public function testCanGetTheMappingsAsAnArray()
    {
        $this->assertTrue(Registry::get('ArrayHandler')->areEquivalent(
            [
                ['old' => 1, 'new' => 101],
                ['old' => 2, 'new' => 102],
                ['old' => 3, 'new' => 103],
                ['old' => 4, 'new' => 104],
            ],
            $this->processor()->getMappingsAsArray('sections')
        ));
    }

    public function testCanGetTheMappingsForASpecifiedField()
    {
        $this->assertSame(
            'Tamandua:Mirim,Ovo:Jacare',
            implode(',', array_map(function($mapping) {
                return implode(':', [
                    $mapping['old'],
                    $mapping['new'],
                ]);
            }, $this->getStub()->callMethod('getMappingsAsArray', [
                'entity' => 'animal',
                'field' => 'generic',
            ])))
        );
    }
}
