<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Util\XmlHandler;

class XmlHandlerTest extends TestCase
{
    private function getStub()
    {
        require_once(dirname(__FILE__) . '/../TestStub.php');
        require_once(dirname(__FILE__) . '/../WorkWithFiles.php');
        return new class extends XmlHandler {
            use BeAmado\OjsMigrator\TestStub;
            use BeAmado\OjsMigrator\WorkWithFiles;
        };
    }

    private function bandsXmlAsArray()
    {
        return [
            'bands' => [
                [
                    'name' => 'Iron Maiden',
                    'albums' => [
                        [
                            'title' => 'Iron Maiden',
                            'year' => '1980',
                            'songs' => [
                                'Prowler',
                                'Sanctuary',
                                'Remember Tomorrow',
                                'Running Free',
                                'The Phantom of the Opera',
                                'Transylvania',
                                'Strange World',
                                'Charlotte the Harlot',
                                'Iron Maiden',
                            ]
                        ],
                        [
                            'title' => 'The number of the beast',
                            'year' => '1983',
                            'songs' => [
                                'Invaders',
                                'Children of the Damned',
                                'The prisoner',
                                '22 Acacia Avenue',
                                'The number of the beast',
                                'Run to the hills',
                                'Gangland',
                                'Total Eclipse',
                                'Hallowed Be Thy Name',
                            ],
                        ],
                        [
                            'title' => 'Somewhere in time',
                            'year' => '1986',
                            'songs' => [
                                'Caught somewhere in time',
                                'Wasted years',
                                'Sea of madness',
                                'Heaven can wait',
                                'The loneliness of the long distance runner',
                                'Stranger in a strange land',
                                'Déjà vu',
                                'Alexander the great',
                            ],
                        ],
                    ],
                ],
                [
                    'name' => 'Helloween',
                    'albums' => [
                        [
                            'title' => 'Keeper of the seven keys - Part I',
                            'songs' => [
                                'Initiation',
                                'I\'m alive',
                                'A little time',
                                'Twilight of the gods',
                                'A tale that wasn\'t right',
                                'Future world',
                                'Halloween',
                                'Follow the sign',
                            ],
                        ],
                    ],
                ],
            ],
        ];
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

        //var_dump($songsNodes);
        //var_dump($songsNodes->item(0));

        $arr = $this->getStub()->callMethod(
            'xmlIntoIndexArray',
            $songsNodes->item(0)
        );

        $this->assertEquals(
            $expected,
            $arr
        );
    }

    public function getIronMaidenAlbumIntoArray()
    {

    }

    /*public function testTransformsXmlIntoArray()
    {
        $xml = $this->readBandsIntoXml();
        
        $arr = $this->getStub()->callMethod(
            'xmlIntoArray',
            $xml
        );

        $this->assertEquals(
            $this->bandsXmlAsArray(),
            $arr
        );

    }*/
}
