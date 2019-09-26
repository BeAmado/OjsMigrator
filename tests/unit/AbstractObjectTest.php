<?php

use \PHPUnit\Framework\TestCase;
use \BeAmado\OjsMigrator\AbstractObject;
use \BeAmado\OjsMigrator\StubInterface;

class AbstractObjectTest extends TestCase implements StubInterface
{
    public function getStub($val = null)
    {
        return new class($val) extends AbstractObject {
            use BeAmado\OjsMigrator\TestStub;

            public function destroyArrayTest(&$arr)
            {
                $this->destroyArray($arr);
            }

            public function destroyObjectTest($obj)
            {
                $this->destroyObject($obj);
            }
        };
    }

    public function testStoresInteger()
    {
        $this->assertEquals(
            41,
            $this->getStub(41)->getValue()
        );
    }

    public function testDestroysInteger()
    {
        $a = $this->getStub(53);
        $a->destroy();
        $this->assertEquals(
            null,
            $a->getValue()
        );
    }

    public function testDestroysArray()
    {
        $a = array(
            'Mago' => 'Suel',
            1 => 'lalala',
            'chew' => array(1, 2, 3),
            'lawries' => array('will' => 'smith'),
        );

        $this->getStub()->destroyArrayTest($a);
        $this->assertEquals(
            array(),
            $a
        );
    }

    public function testDestroysObject()
    {
        $prototype = new class {};

        $o = clone $prototype;

        $o->attr1 = 'lalala';
        $o->attr2 = 'waba';

        $this->getStub()->destroyObjectTest($o);

        $this->assertEquals(
            (clone $prototype),
            $o
        );
    }
}
