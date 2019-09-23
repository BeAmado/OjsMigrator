<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Util\FileHandler;
use BeAmado\OjsMigrator\Util\FileSystemManager;

class FileHandlerTest extends TestCase
{
    private function getStub()
    {
        require_once(dirname(__FILE__) . '/../TestStub.php');
        return new class extends FileHandler {
            use BeAmado\OjsMigrator\TestStub;
        };
    }

    private function getDataDir()
    {
        return (new FileSystemManager())->parentDir(dirname(__FILE__)) 
            . '/_data';
    }

    private function getHumptyDumptyFile()
    {
        return $this->getDataDir() . '/humpty_dumpty.txt';
    }

    public function testReadHumptyDumptyFile()
    {
        $str = (new FileHandler())->read($this->getHumptyDumptyFile());
        $this->assertSame(
            'Humpty Dumpty sat on a wall' . PHP_EOL,
            (new FileHandler())->read($this->getHumptyDumptyFile())
        );
    }

    public function testWriteShrekFile()
    {
        $str = 'Ogres are like onions!';
        $shrekFile = $this->getDataDir() . '/shrek.txt';
        (new FileHandler())->write($shrekFile, $str);

        $this->assertSame(
            $str,
            (new FileHandler())->read($shrekFile)
        );

        (new FileSystemManager())->removeFile($shrekFile);
    }
}
