<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Util\MemoryManager;

class MemoryManagerTest extends TestCase
{
    private function getStub()
    {
        require_once(dirname(__FILE__) . '/../TestStub.php');
        return new class extends MemoryManager {
            use BeAmado\OjsMigrator\TestStub;
        };
    }
    
    public function testCanInstantiateMemoryManager()
    {
        $this->assertInstanceOf(
            'BeAmado\OjsMigrator\Util\MemoryManager',
            new MemoryManager()
        );
    }

    public function testCreate()
    {
        $this->assertSame(
            13,
            (new MemoryManager())->create(13)->getValue()
        );
    }

    /*public function testDestroy()
    {
        $a = 13;
        (new MemoryManager())->destroy($a);
        $this->assertFalse(isset($a));
    }*/

}
