<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Util\JsonHandler;

class JsonHandlerTest extends TestCase
{
    private function getStub()
    {
        require_once(dirname(__FILE__) . '/../TestStub.php');
        return new class extends JsonHandler {
            use BeAmado\OjsMigrator\TestStub;
        };
    }

    private function getBandsFilename()
    {
        return dirname(__FILE__) . '/../_data/bands.json';
    }

    public function testCanReadTheBandsJsonFile()
    {
        $bands = (new JsonHandler())->createFromFile($this->getBandsFilename());
        $this->assertInstanceOf(
            \BeAmado\OjsMigrator\MyObject::class,
            $bands
        );
    }

    public function testReadsIronMaidensAlbumsFromBandsFile()
    {
        $bands = (new JsonHandler())->createFromFile($this->getBandsFilename());
        $this->assertSame(
            $bands->get('Iron Maiden')
                  ->get('albums')
                  ->get(0)
                  ->get('songs')
                  ->get(0)->getValue(),
            'Prowler'
        );
    }
}
