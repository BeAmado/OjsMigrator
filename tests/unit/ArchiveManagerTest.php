<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Util\ArchiveManager;
use BeAmado\OjsMigrator\StubInterface;
use BeAmado\OjsMigrator\Util\FileHandler;
use BeAmado\OjsMigrator\Util\FileSystemManager;

class ArchiveManagerTest extends TestCase implements StubInterface
{
    public function getStub()
    {
        return new class extends ArchiveManager {
            use BeAmado\OjsMigrator\TestStub;
            use BeAmado\OjsMigrator\WorkWithFiles;
        };
    }

    protected function setUp(): void
    {
        $this->sandbox = $this->getStub()->getDataDir() . '/sandbox';
        (new FileSystemManager())->createDir($this->sandbox);
    }

    protected function tearDown(): void
    {
        (new FileSystemManager())->removeWholeDir($this->sandbox);
    }

    protected function getTempDirName()
    {
        return $this->sandbox . '/temp1';
    }

    protected function listTempDirContent()
    {
        return array(
            'files' => array(
                'evil-that-men-do.txt',
                'rime-of-the-ancient-mariner.txt',
             ),
             'dirs' => array(
                array(
                    'name' => 'inner',
                    'files' => array(
                        'inner/the-deeper-the-love.txt',
                    ),
                ),
             ),
        );
    }

    protected function prependPathToDirContent($dirlist, $path)
    {
        $files = array();
        $dirs = array();

        if (array_key_exists('files', $dirlist)) {
            $files = $dirlist['files'];
        }

        if (array_key_exists('dirs', $dirlist)) {
            $dirs = $dirlist['dirs'];
        }

        for ($i = 0; $i < count($files); $i++) {
            $files[$i] = $path . '/' . $files[$i];
        }

        for ($i = 0; $i < count($dirs); $i++) {
            $dirs[$i] = $this->prependPathToDirContent($dirs[$i], $path);
        }

        $arr = array();

        if (array_key_exists('files', $dirlist)) {
            $arr['files'] = $files;
        }

        if (array_key_exists('dirs', $dirlist)) {
            $arr['dirs'] = $dirs;
        }

        if (array_key_exists('name', $dirlist)) {
            $arr['name'] = $path . '/' . $dirlist['name'];
        }

        return $arr;
    }

    protected function createTempDirWithFiles()
    {
        $temp1 = $this->getTempDirName();

        (new FileSystemManager())->createDir($temp1);

        (new FileHandler())->write(
            $temp1 . '/evil-that-men-do.txt',
            'The evil that man do lives on and on...'
        );

        (new FileHandler())->write(
            $temp1 . '/rime-of-the-ancient-mariner.txt',
            'Sailing on and on and on across the sea...'
        );

        $inner = $temp1 . '/inner';

        (new FileSystemManager())->createDir($inner);

        (new FileHandler())->write(
            $inner . '/the-deeper-the-love.txt',
            'The deeper the love' . PHP_EOL . 'The stronger the emotion'
        );
    }

    public function testGetTheParametersToTheTarFunction()
    {
        $this->assertEquals(
            ['x', 'z', 'v', 'f'],
            $this->getStub()->callMethod(
                'getTarParams',
                'xzvf'
            )
        );
    }

    public function testCreateTarFile()
    {
        $this->createTempDirWithFiles();
        $tarFilename = $this->getTempDirName() . '.tar';

        $this->getStub()->callMethod(
            'createTar',
            array(
                'filename' => $tarFilename,
                'directory' => $this->getTempDirName(),
            )
        );

        $this->assertTrue(
            (new FileSystemManager())->fileExists($tarFilename)
        );
    }

    public function testCanTarSomeDirectory()
    {
        $this->createTempDirWithFiles();
        $tarFilename = $this->getTempDirName() . '.tar';

        $this->assertTrue(
            (new ArchiveManager())->tar(
                'cf', 
                $tarFilename, 
                $this->getTempDirName()
            ) &&
            (new FileSystemManager())->fileExists($tarFilename)
        );
    }

    public function testExtractTarFile()
    {
        $this->createTempDirWithFiles();
        $tarFilename = $this->getTempDirName() . '.tar';

        (new ArchiveManager())->tar(
            'cf', 
            $tarFilename, 
            $this->getTempDirName()
        );

        $tempToExtract = $this->sandbox . '/extractHere';
        (new FileSystemManager())->createDir($tempToExtract);

        $result = $this->getStub()->callMethod(
            'extractTar',
            array(
                'filename' => $tarFilename,
                'pathTo' => $tempToExtract,
            )
        );

        $dirlist = $this->prependPathToDirContent(
            $this->listTempDirContent(),
            $tempToExtract
        );

        $this->assertTrue(
            $result &&
            (new FileSystemManager())->fileExists(
                $dirlist['files'][0]
            ) &&
            (new FileSystemManager())->fileExists(
                $dirlist['files'][1]
            ) &&
            (new FileSystemManager())->dirExists(
                $dirlist['dirs'][0]['name']
            ) &&
            (new FileSystemManager())->fileExists(
                $dirlist['dirs'][0]['files'][0]
            )
        );
    }

    public function testCanUntarTarball()
    {
        $this->createTempDirWithFiles();
        $tarFilename = $this->getTempDirName() . '.tar';

        (new ArchiveManager())->tar(
            'cf', 
            $tarFilename, 
            $this->getTempDirName()
        );

        $tempToExtract = $this->sandbox . '/extractHere';
        (new FileSystemManager())->createDir($tempToExtract);

        $this->assertTrue(
            (new ArchiveManager())->tar(
                'xf', 
                $tarFilename, 
                $tempToExtract
            ) &&
            (new FileSystemManager())->fileExists(
                $tempToExtract . '/evil-that-men-do.txt'
            )
        );
    }

    public function testCanTarAndCompressADirectory()
    {
        $this->createTempDirWithFiles();
        //$createdFilename = $this->getTempDirName() . '.tar.gz';

        $this->assertTrue(
            (new ArchiveManager())->tar(
                'czf',
                $this->getTempDirName(),
                $this->getTempDirName()
            ) &&
            (new FileSystemManager())->fileExists(
                $this->getTempDirName() . '.tar.gz'
            )
        );
    }

    public function testCanUncompressAndUntarAFile()
    {
        $this->createTempDirWithFiles();

        (new ArchiveManager())->tar(
            'czf',
            $this->getTempDirName(),
            $this->getTempDirName()
        );


        $this->assertTrue(
            //removing the temp directory
            (new FileSystemManager())->removeWholeDir(
                $this->getTempDirName()
            ) &&
            !(new FileSystemManager())->dirExists($this->getTempDirName()) &&

            //extracting the tarball and creating the temp directory with its content
            (new ArchiveManager())->tar(
                'xzf',
                $this->getTempDirName(),
                $this->getTempDirName()
            ) &&
            (new FileSystemManager())->dirExists($this->getTempDirName()) &&
            (new FileSystemManager())->fileExists(
                $this->getTempDirName ()
                . \BeAmado\OjsMigrator\DIR_SEPARATOR
                . 'evil-that-men-do.txt'
            )
        );

    }
}
