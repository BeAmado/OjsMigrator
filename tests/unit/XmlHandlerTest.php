<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Util\XmlHandler;
use BeAmado\OjsMigrator\StubInterface;

class XmlHandlerTest extends TestCase implements StubInterface
{
    public function getStub()
    {
        return new class extends XmlHandler {
            use BeAmado\OjsMigrator\TestStub;
            use BeAmado\OjsMigrator\WorkWithFiles;
        };
    }

    private function getBandsXmlFilename()
    {
        return $this->getStub()->getDataDir() . '/bands.xml';
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
            'Prowler',
            'Sanctuary',
            'Remember Tomorrow',
            'Running Free',
            'The Phantom of the Opera',
            'Transylvania',
            'Strange World',
            'Charlotte the Harlot',
            'Iron Maiden',
        );

        $arr = $this->getStub()->callMethod(
            'xmlIntoIndexArray',
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

        $expected = $this->getStub()->bandsAsArray()['bands'][0]['albums'][0];

        $arr = $this->getStub()->callMethod(
            'xmlIntoAssocArray',
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
            $this->getStub()->bandsAsArray()['bands'][0]['albums'],
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
            $this->getStub()->bandsAsArray(),
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
            $obj->get('bands')->get(1)->get('name')->getValue()
        );

        $obj->destroy();
        unset($obj);
    }

    public function testCreateFromFileAndTurnIntoArray()
    {
        $this->assertEquals(
            $this->getStub()->bandsAsArray(),
            (new XmlHandler())->createFromFile(
                $this->getBandsXmlFilename()
            )->toArray()
        );
    }
}
