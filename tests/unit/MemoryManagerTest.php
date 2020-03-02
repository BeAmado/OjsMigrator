<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Util\MemoryManager;

// interfaces
use BeAmado\OjsMigrator\Test\StubInterface;

// traits
use BeAmado\OjsMigrator\Test\TestStub;

class MemoryManagerTest extends TestCase implements StubInterface
{
    public function getStub()
    {
        return new class extends MemoryManager {
            use TestStub;
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

    public function testCreateEmptyObject()
    {
        $this->assertInstanceOf(
            \BeAmado\OjsMigrator\MyObject::class,
            (new MemoryManager())->create()
        );
    }

    /*public function testDestroy()
    {
        $a = 13;
        (new MemoryManager())->destroy($a);
        $this->assertFalse(isset($a));
    }*/

}
