<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\DataMapper;
use BeAmado\OjsMigrator\Registry;

// interfaces
use BeAmado\OjsMigrator\Test\StubInterface;

// traits
use BeAmado\OjsMigrator\Test\TestStub;

// helper 
use BeAmado\OjsMigrator\Test\DataMappingHandler;

class DataMapperTest extends TestCase implements StubInterface
{
    public static function setUpBeforeClass() : void
    {
        (new DataMappingHandler())->setUpDataMappingStage();
    }

    public static function tearDownAfterClass() : void
    {
        (new DataMappingHandler())->tearDownDataMappingStage();
    }

    public function getStub()
    {
        return new class extends DataMapper {
            use TestStub;
        };
    }


    public function testCanFormUser54823MappingFilename()
    {
        $expected = Registry::get('FileSystemManager')->formPathFromBaseDir(
            array(
                'tests',
                '_data',
                'sandbox',
                'data_mapping',
                'users',
                '1-100000',
                '50001-60000',
                '54001-55000',
                '54801-54900',
                '54821-54830',
                '54823',
            )
        );

        $filename = $this->getStub()->callMethod(
            'formMappingFilename',
            array(
                'entityName' => 'users',
                'id' => 54823,
            )
        );

        $this->assertSame($expected, $filename);
    }

    public function testCanGetSectionsMappingDirectory()
    {
        $expected = Registry::get('FileSystemManager')->formPathFromBaseDir(
            array(
                'tests',
                '_data',
                'sandbox',
                'data_mapping',
                'sections',
            )
        );

        $dir = Registry::get('DataMapper')->getEntityMappingDir('sections');

        $this->assertSame($expected, $dir);
    }

    public function testCanSeeThatArticleFile2367IsNotMapped()
    {
        $this->assertFalse(Registry::get('DataMapper')->isMapped(
            'article_files',
            2367
        ));
    }

    public function testCanMapSectionId23To2735()
    {
        $mapped = Registry::get('DataMapper')->mapData('sections', array(
            'old' => 23,
            'new' => 2735,
        ));

        $map = Registry::get('FileSystemManager')->formPath(array(
            Registry::get('DataMapper')->getEntityMappingDir('sections'),
            '1-100',
            '21-30',
            '23',
        ));

        $fileExists = Registry::get('FileSystemManager')->fileExists($map);

        $content = Registry::get('FileHandler')->read($map);

        $this->assertTrue(
            $mapped === true &&
            $fileExists === true &&
            $content === '2735'
        );
    }

    /**
     * @depends testCanMapSectionId23To2735
     */
    public function testCanSeeThatSection23IsMapped()
    {
        $this->assertTrue(
            Registry::get('DataMapper')->isMapped('sections', 23)
        );
    }

    /**
     * @depends testCanMapSectionId23To2735
     */
    public function testCanSeeThatSection23IsMappedTo2735()
    {
        $this->assertEquals(
            2735,
            Registry::get('DataMapper')->getMapping('sections', 23)
        );
    }

    public function testCanMapSectionId8467To267()
    {
        $mapped = Registry::get('DataMapper')->mapData('sections', array(
            'old' => 8467,
            'new' => 267,
        ));
        
        $mapping = Registry::get('DataMapper')->getMapping('sections', 8467);

        $this->assertTrue(
            $mapped === true &&
            $mapping === '267' &&
            count(Registry::get('FileSystemManager')->listdir(
                Registry::get('DataMapper')->getEntityMappingDir('sections')
            )) === 1
        );
    }

    /**
     * @depends testCanMapSectionId23To2735
     * @depends testCanMapSectionId8467To267
     */
    public function testCanGetTheMappingsForSections23And8467()
    {
        $section23NewId = Registry::get('DataMapper')->getMapping(
            'sections',
            23
        );

        $section8467NewId = Registry::get('DataMapper')->getMapping(
            'sections',
            8467
        );

        $this->assertTrue(
            $section23NewId === '2735' &&
            $section8467NewId === '267'
        );
    }
}
