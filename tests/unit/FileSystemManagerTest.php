<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Util\FileSystemManager;

class FileSystemManagerTest extends TestCase
{
    private function getStub()
    {
        require_once(dirname(__FILE__) . '/../TestStub.php');
        return new class extends FileSystemManager {
            use BeAmado\OjsMigrator\TestStub;
        };
    }

    public function testRemovesAllDotsAndDoubleDots()
    {
        $this->assertSame(
            array(1, 2, 3),
            $this->getStub()->callMethod(
                'removeDots',
                array('list' => array(1, '.', '..', '.', '..', 2, '.', '.', '..', 3, '..', '.', '..'))
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
            'classes',
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
            $this->getStub()->callMethod('removeTrailingSlashes', '/home/feynman/lectures')
        );
    }

    public function testRemovesAllSixTrailingSlashes()
    {
        $this->assertEquals(
            '/home/feynman/lectures',
            $this->getStub()->callMethod('removeTrailingSlashes', '/home/feynman/lectures//////')
        );
    }
}
