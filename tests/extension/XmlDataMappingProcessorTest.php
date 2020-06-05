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
//    public function testCanReadTheXmlIntoAnObject()
//    {
//        $filename = $this->getStub()->callMethod(
//            'formMappingFilename',
//            'sections'
//        );
//
//        $obj = Registry::get('XmlHandler')->createFromFile($filename);
//
//        $this->assertSame(
//            '4-1:101,2:102,3:103,4:104',
//            implode('-', [
//                $obj->get('children')->length(),
//                implode(',', array_map(function($mapping) {
//                    $old = null;
//                    $new = null;
//                    foreach ($mapping['children'] as $item) {
//                        if ($item['name'] === 'old')
//                            $old = $item['text'];
//                        else if ($item['name'] === 'new')
//                            $new = $item['text'];
//                    }
//
//                    return implode(':', [$old, $new]);
//
//                }, $obj->get('children')->toArray()))
//            ])
//        );
//    }
//
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
}
