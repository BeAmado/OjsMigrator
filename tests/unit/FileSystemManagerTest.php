<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Util\FileSystemManager;
use BeAmado\OjsMigrator\Registry;

// interfaces
use BeAmado\OjsMigrator\Test\StubInterface;

// traits
use BeAmado\OjsMigrator\Test\TestStub;
use BeAmado\OjsMigrator\Test\WorkWithFiles;

class FileSystemManagerTest extends TestCase implements StubInterface
{
    public static function tearDownAfterClass() : void
    {
        Registry::get('FileSystemManager')->removeWholeDir(
            Registry::get('FileSystemManager')->formPathFromBaseDir(array(
                'tests',
                '_data',
                'sandbox',
            ))
        );
    }

    public function getStub()
    {
        return new class extends FileSystemManager {
            use TestStub;
            use WorkWithFiles;
        };
    }

    public function testRemovesAllDotsAndDoubleDots()
    {
        $this->assertSame(
            [1, 2, 3],
            $this->getStub()->callMethod(
                'removeDots',
                ['list' => [1, '.', '..', '.', '..', 2, '.',  3, '..', '.']]
            )
        );
    }

    public function testParentDirOfHomeFeynmanIsHome()
    {
        $this->assertEquals(
            (new FileSystemManager())->parentDir('/home/Feynman'),
            '/home'
        );
    }

    public function testParentDirOfHomeIsTheRootDir()
    {
        $this->assertEquals(
            (new FileSystemManager())->parentDir('/home'),
            '/'
        );
    }

    public function testParentOfRootDirIsTheRootDir()
    {
        $this->assertEquals(
            (new FileSystemManager())->parentDir('/'),
            '/'
        );
    }

    public function testTwoLevelsUpTheUnitTestsDirIsTheBaseDir()
    {
        $this->assertEquals(
            (new FileSystemManager())->goUp(\dirname(__FILE__), 2),
            BeAmado\OjsMigrator\BASE_DIR
        );
    }

    public function testCanScanTheDirectories()
    {
        $this->assertIsArray((new FileSystemManager())->listdir());
    }

    public function testSeesTheClassesDirectory()
    {
        $this->assertContains(
            \BeAmado\OjsMigrator\LIB_DIR . \BeAmado\OjsMigrator\DIR_SEPARATOR . 'classes',
            (new FileSystemManager())->listdir(BeAmado\OjsMigrator\LIB_DIR)
        );
    }

    public function testRemovesTheTrailingSlash()
    {
        $this->assertEquals(
            '/home/feynman/lectures',
            $this->getStub()->callMethod('removeTrailingSlashes', '/home/feynman/lectures/')
        );
    }

    public function testNoTrailingSlashes()
    {
        $this->assertEquals(
            '/home/feynman/lectures',
            $this->getStub()->callMethod(
                'removeTrailingSlashes', 
                '/home/feynman/lectures'
            )
        );
    }

    public function testRemovesAllSixTrailingSlashes()
    {
        $this->assertEquals(
            '/home/feynman/lectures',
            $this->getStub()->callMethod(
                'removeTrailingSlashes',
                '/home/feynman/lectures//////'
            )
        );
    }

    public function testFormsPathCorrectly()
    {
        $sep = \BeAmado\OjsMigrator\DIR_SEPARATOR;
        $this->assertEquals(
            'path' . $sep . 'to' . $sep . 'dir',
            (new FileSystemManager())->formPath([
                'path',
                'to',
                'dir',
            ])
        );
    }

    public function testFormsFullPathWithBaseDirectoryCorrectly()
    {
        $sep = \BeAmado\OjsMigrator\DIR_SEPARATOR;
        $this->assertEquals(
            \BeAmado\OjsMigrator\BASE_DIR 
            . $sep . 'path' 
            . $sep . 'to' 
            . $sep . 'dir',
            (new FileSystemManager())->formPathFromBaseDir([
                'path',
                'to',
                'dir',
            ])
        );
    }

    public function testDirectoryTempDoesNotExist()
    {
        $this->assertFalse(
            is_dir(
                (new FileSystemManager())->formPath([
                    dirname(__FILE__),
                    'temp',
                ])
            )
        );
    }
    
    /**
     * @depends testDirectoryTempDoesNotExist
     */
    public function testDirectoryDoesNotExistWithArray()
    {

        $this->assertFalse(
            (new FileSystemManager())->dirExists(
                explode(
                    \BeAmado\OjsMigrator\DIR_SEPARATOR,
                    (new FileSystemManager())->formPath(array(
                        dirname(__FILE__), 
                        'temp',
                    ))
                )
            )
        );

        unset($dir);
    }

    /**
     * @depends testDirectoryTempDoesNotExist
     */
    public function testDirectoryDoesNotExistWithString()
    {
        $this->assertFalse(
            (new FileSystemManager())->dirExists(
                (new FileSystemManager())->formPath(array(
                    dirname(__FILE__),
                    'temp'
                 ))
            )
        );
    }

    /**
     * @depends testDirectoryTempDoesNotExist
     */
    public function testCreateDirectory()
    {
        $dir = (new FileSystemManager())->formPath(array(
            dirname(__FILE__) ,
            'temp'
        ));
        (new FileSystemManager())->createDir($dir);

        $this->assertTrue(is_dir($dir));

        unset($dir);
    }

    /**
     * @depends testCreateDirectory
     */
    public function testRemovesEmptyDirectory()
    {
        $dir = (new FileSystemManager())->formPath(array(
            dirname(__FILE__) ,
            'temp'
        ));

        (new FileSystemManager())->removeDir($dir);
        
        $this->assertFalse(is_dir($dir));
    }

    public function testCanCreateFile()
    {
        $filename = (new FileSystemManager())->formPath(array(
            dirname(__FILE__),
            'file.txt'
        ));

        $this->assertTrue(
            (new FileSystemManager())->createFile($filename) &&
            is_file($filename)
        );
    }

    /**
     * @depends testCanCreateFile
     */
    public function testCanRemoveFile()
    {
        $filename = (new FileSystemManager())->formPath(array(
            dirname(__FILE__),
            'file.txt'
        ));
        
        $this->assertTrue(
            (new FileSystemManager())->removeFile($filename) &&
            !is_file($filename)
        );
    }

    /**
     * @depends testRemovesEmptyDirectory
     */
    public function testRemoveWholeDir()
    {
        
        $dir = (new FileSystemManager())->formPath(array(
            dirname(__FILE__) ,
            'temp',
            'level1'
        ));
        (new FileSystemManager())->createDir($dir);
        (new FileSystemManager())->createFile(
            $dir . \BeAmado\OjsMigrator\DIR_SEPARATOR . 'file1.txt'
        );

        $parentDir = (new FileSystemManager())->parentDir($dir);
        (new FileSystemManager())->createFile(
            $parentDir . \BeAmado\OjsMigrator\DIR_SEPARATOR . 'file2.txt'
        );
        
        $this->assertTrue(
            \is_dir($parentDir) &&
            (new FileSystemManager())->removeWholeDir($parentDir) &&
            !\is_dir($parentDir)
        );
    }

    public function testCopyFile()
    {
        $originalFilename = $this->getStub()->getDataDir()
            . \BeAmado\OjsMigrator\DIR_SEPARATOR . 'humpty_dumpty.txt';

        $copiedFilename = $this->getStub()->getDataDir() 
            . \BeAmado\OjsMigrator\DIR_SEPARATOR . 'humpty_copy.txt';

        $this->assertTrue(
            (new FileSystemManager())->copyFile(
                $originalFilename,
                $copiedFilename
            ) &&
            (new FileSystemManager())->fileExists($copiedFilename)
        );

        if ((new FileSystemManager())->fileExists($copiedFilename)) {
            (new FileSystemManager())->removeFile($copiedFilename);
        }
    }

    protected function sandbox()
    {
        return $this->getStub()->formPath(array(
            $this->getStub()->getDataDir(),
            'sandbox'
        ));
    }

    protected function createSandbox()
    {
        if (!Registry::get('FileSystemManager')->dirExists($this->sandbox()))
            Registry::get('FileSystemManager')->createDir($this->sandbox());
    }

    protected function removeSandbox()
    {
        $this->getStub()->removeWholeDir($this->sandbox());
    }

    public function testMoveDirectory()
    {
        $this->createSandbox();
        $from = $this->getStub()->formPath(array($this->sandbox(), 'lala'));
        $to = $this->getStub()->formPath(array($this->sandbox(), 'lele'));

        $this->getStub()->createDir($from);

        $filename = 'ratata';
        $fromFilename = $from . \BeAmado\OjsMigrator\DIR_SEPARATOR . $filename;

        Registry::get('FileHandler')->write(
            $fromFilename,
            'Ratikate'
        );

        $toFilename = $to . \BeAmado\OjsMigrator\DIR_SEPARATOR . $filename;

        $moved = $this->getStub()->move($from, $to);

        $this->assertTrue(
            $this->getStub()->fileExists($toFilename) &&
            Registry::get('filehandler')->read($toFilename) == 'Ratikate' &&
            !$this->getStub()->dirExists($from)
        );

        $this->removeSandbox();
    }

    public function testMoveDirectoryToAnExistingOneMergingTheirContents()
    {
        $this->createSandbox();

        $fsm = Registry::get('FileSystemManager');

        $destination = $fsm->formPath(array(
            $this->sandbox(),
            'destination',
        ));

        $origin = $fsm->formPath(array(
            $this->sandbox(),
            'origin',
        ));

        foreach (array(
            $destination,
            $fsm->formPath(array($destination, '100')),
            $origin,
            $fsm->formPath(array($origin, '100')),
        ) as $directory) {
            $fsm->createDir($directory);
        }

        foreach (array(
            $fsm->formPath(array(
                $destination,
                '100',
                '21',
            )),
            $fsm->formPath(array(
                $destination,
                '100',
                '22',
            )),
            $fsm->formPath(array(
                $origin,
                '100', 
                '77',
            )),
            $fsm->formPath(array(
                $origin,
                '100',
                '78',
            )),
        ) as $file) {
            $fsm->createFile($file);
        }

        $moved = $fsm->move(
            $fsm->formPath(array($origin, '100')),
            $fsm->formPath(array($destination, '100'))
        );

        $this->assertSame(
            '1-0-4',
            implode('-', array(
                (int) $moved,
                (int) $fsm->dirExists(
                    $fsm->formPath(array($origin, '100'))
                ),
                count($fsm->listdir(
                    $fsm->formPath(array($destination, '100'))
                )), 
            ))
        );

        $this->removeSandbox();
    }
}
