<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Util\XmlHandler;

// interfaces
use BeAmado\OjsMigrator\Test\StubInterface;

// traits
use BeAmado\OjsMigrator\Test\TestStub;
use BeAmado\OjsMigrator\Test\WorkWithFiles;

class XmlHandlerTest extends TestCase implements StubInterface
{
    use WorkWithFiles;

    public function getStub()
    {
        return new class extends XmlHandler {
            use TestStub;
            use WorkWithFiles;
        };
    }

    private function getBandsXmlFilename()
    {
        return $this->getDataDir() . $this->sep() . 'bands.xml';
    }

    private function getAnimalsXmlFilename()
    {
        return $this->getDataDir() . $this->sep() . 'animals.xml';
    }

    private function readBandsIntoXml()
    {
        return $this->getStub()->callMethod(
            'readXml',
            $this->getBandsXmlFilename()
        );
    }

    public function testReadsXml()
    {
        $this->assertInstanceOf(
            DOMDocument::class,
            $this->getStub()->callMethod(
                'readXml',
                $this->getBandsXmlFilename()
            )
        );
    }

    public function testSongIsTextNode()
    {
        $xml = $this->readBandsIntoXml();

        $songNodes = $xml->getElementsByTagName('song');

        $this->assertTrue($this->getStub()->callMethod(
            'isTextNode',
            $songNodes->item(0)
        ));
    }

    public function testFirstSongTextNodeIsProwler()
    {
        $xml = $this->readBandsIntoXml();

        $songNodes = $xml->getElementsByTagName('song');

        $this->assertSame(
            'Prowler',
            $this->getStub()->callMethod(
                'getTextContent',
                $songNodes->item(0)
            )
        );
    }

    public function testFirstAlbumWhichIsIronMaidenHasNineSongs()
    {
        $xml = $this->readBandsIntoXml();

        $songsNodes = $xml->getElementsByTagName('songs');
        
        $this->assertSame(
            9,
            \count($this->getStub()->callMethod(
                'getChildNodes',
                $songsNodes->item(0)
            ))
        );
    }

    public function testSongsNodeIsIndexedArray()
    {
        $xml = $this->readBandsIntoXml();

        $songsNodes = $xml->getElementsByTagName('songs');

        $this->assertSame(
            'indexed',
            $this->getStub()->callMethod(
                'arrayType',
                $songsNodes->item(0)
            )
        );
    }

    public function testBandNodeIsAssociativeArray()
    {
        $xml = $this->readBandsIntoXml();

        $bandNodes = $xml->getElementsByTagName('band');

        $this->assertSame(
            'associative',
            $this->getStub()->callMethod(
                'arrayType',
                $bandNodes->item(0)
            )
        );
    }

    public function testGetSongsIntoArray()
    {
        $xml = $this->readBandsIntoXml();
        
        $songsNodes = $xml->getElementsByTagName('songs');

        $expected = array(
            'name' => 'songs',
            'text' => null,
            'attributes' => array(),
            'children' => array(
                array(
                    'name' => 'song',
                    'text' => 'Prowler',
                    'attributes' => array(),
                    'children' => array(),
                ),
                array(
                    'name' => 'song',
                    'text' => 'Sanctuary',
                    'attributes' => array(),
                    'children' => array(),
                ),
                array(
                    'name' => 'song',
                    'text' => 'Remember Tomorrow',
                    'attributes' => array(),
                    'children' => array(),
                ),
                array(
                    'name' => 'song',
                    'text' => 'Running Free',
                    'attributes' => array(),
                    'children' => array(),
                ),
                array(
                    'name' => 'song',
                    'text' => 'The Phantom of the Opera',
                    'attributes' => array(),
                    'children' => array(),
                ),
                array(
                    'name' => 'song',
                    'text' => 'Transylvania',
                    'attributes' => array(),
                    'children' => array(),
                ),
                array(
                    'name' => 'song',
                    'text' => 'Strange World',
                    'attributes' => array(),
                        'children' => array(),
                ),
                array(
                    'name' => 'song',
                    'text' => 'Charlotte the Harlot',
                    'attributes' => array(),
                    'children' => array(),
                ),
                array(
                    'name' => 'song',
                    'text' => 'Iron Maiden',
                    'attributes' => array(),
                    'children' => array(),
                ),
            ),
        );

        $arr = $this->getStub()->callMethod(
            'xmlIntoArray',
            $songsNodes->item(0)
        );

        $this->assertEquals(
            $expected,
            $arr
        );
    }

    public function testGetIronMaidenAlbumIntoArray()
    {
        $xml = $this->readBandsIntoXml();

        $album = $xml->getElementsByTagName('album')->item(0);

        $expected = $this->bandsAsVerboseArray()
            ['children'][0]  // Iron Maiden
            ['children'][1]  // albums
            ['children'][0]; // Iron Maiden album

        $arr = $this->getStub()->callMethod(
            'xmlIntoArray',
            $album
        );

        $this->assertEquals(
            $expected,
            $arr
        );
    }

    public function testGetIronMaidensAlbumsIntoArray()
    {
        $xml = $this->readBandsIntoXml();
        
        $arr = $this->getStub()->callMethod(
            'xmlIntoArray',
            $xml->getElementsByTagName('albums')->item(0)
        );

        $this->assertEquals(
            $this->bandsAsVerboseArray()
                ['children'][0]  // Iron Maiden
                ['children'][1], // albums
            $arr
        );

    }

    public function testBandsNodeIsTheRootNode()
    {
        $xml = $this->readBandsIntoXml();

        $this->assertTrue(
            $this->getStub()->callMethod(
                'isRootNode',
                $xml
            )
        );
    }

    public function testReadXmlIntoArray()
    {
        $this->assertEquals(
            $this->bandsAsVerboseArray(),
            $this->getStub()->callMethod(
                'readIntoArray',
                $this->getBandsXmlFilename()
            )
        );
    }

    public function testCreateObjectFromXmlFile()
    {
        $obj = (new XmlHandler())->createFromFile(
            $this->getBandsXmlFilename()
        );

        $this->assertSame(
            'Helloween',
            $obj
            ->get('children')->get(1) // Helloween
            ->get('children')->get(0) // name
            ->get('text')->getValue()
        );

        $obj->destroy();
        unset($obj);
    }

    public function testCreateFromFileAndTurnIntoArray()
    {
        $this->assertEquals(
            $this->bandsAsVerboseArray(),
            (new XmlHandler())->createFromFile(
                $this->getBandsXmlFilename()
            )->toArray()
        );
    }

    public function testGetAttributes()
    {
        $xml = new DOMDocument('1.0', 'UTF-8');
        $xml->load($this->getAnimalsXmlFilename());

        $dog = $xml->getElementsByTagName('animal')->item(0);

        $this->assertEquals(
            array('class' => 'mammalia'),
            $this->getStub()->callMethod(
                'getAttributes',
                $dog
            )
        );
    }
}
