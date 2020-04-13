<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Util\JsonHandler;
use BeAmado\OjsMigrator\Util\MemoryManager;
use BeAmado\OjsMigrator\Util\FileSystemManager;
use BeAmado\OjsMigrator\Util\FileHandler;
use BeAmado\OjsMigrator\Registry;

// interfaces 
use BeAmado\OjsMigrator\Test\StubInterface;

// traits
use BeAmado\OjsMigrator\Test\TestStub;
use BeAmado\OjsMigrator\Test\WorkWithFiles;

class JsonHandlerTest extends TestCase implements StubInterface
{
    public function getStub()
    {
        return new class extends JsonHandler {
            use TestStub;
            use WorkWithFiles;
        };
    }

    private function getBandsFilename()
    {
        return $this->getStub()->getDataDir() . '/bands.json';
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

    public function testObjectToJsonFile()
    {
        $obj = (new MemoryManager())->create();
        $obj->set(
            'singers',
            array(
                'Bruce Dickinson',
                'Paul Dianno',
                'Blaze Bailey',
            )
        );

        $maidenFile = $this->getStub()->getDataDir() . '/maiden.json';

        $obj->set('band', 'Iron Maiden');

        (new JsonHandler())->dumpToFile($maidenFile, $obj);

        $json = json_decode((new FileHandler())->read($maidenFile), true);

        $this->assertTrue(
            $json['band'] === 'Iron Maiden' &&
            count($json['singers']) === 3
        );

        (new FileSystemManager())->removeFile($maidenFile);
    }

    public function testCanSeeWhetherOrNotAFileHasJsonExtension()
    {
        $jh = Registry::get('JsonHandler');
        $this->assertSame(
            '1-0-0',
            implode('-', [
                (int) $jh->hasJsonExtension('Trump.json'),
                (int) $jh->hasJsonExtension('Journalist'),
                (int) $jh->hasJsonExtension('lai'),
            ])
        );
    }

    public function testJsonFileMethodGivesAFileTheJsonExtensionIfItHasNot()
    {
        $jh = Registry::get('JsonHandler');
        $file1 = 'lala.json';
        $file2 = 'Johnny';

        $this->assertSame(
            '1-1',
            implode('-', [
                (int) ($jh->jsonFile($file1) === 'lala.json'),
                (int) ($jh->jsonFile($file2) === 'Johnny.json'),
            ])
        );
    }
}
