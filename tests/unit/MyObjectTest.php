<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\MyObject;

class MyObjectTest extends TestCase
{
    private function getStub()
    {
        require_once(dirname(__FILE__) . '/../TestStub.php');
        return new class extends MyObject {
            use BeAmado\OjsMigrator\TestStub;
        };
    }

    public function testCanInstantiate()
    {
        $this->assertInstanceOf(
            MyObject::class,
            (new MyObject())
        );
    }

    public function testStoresNumber()
    {
        $this->assertEquals(
            21,
            (new MyObject(21))->getValue()
        );
    }

    public function testDestroysValue()
    {
        $a = new MyObject('lalala');
        $a->destroy();

        $this->assertSame(
            null,
            $a->getValue()
        );
    }

    public function testOneLevelArray()
    {
        $arr = array(
            'name' => 'Bernardo',
            'movie' => 'Lord of the rings',
        );

        $arrObj = new MyObject($arr);

        $this->assertEquals(
            $arr,
            array(
                'name' => $arrObj->get('name')->getValue(),
                'movie' => $arrObj->get('movie')->getValue()
            )
        );
    }

    public function testCreateEmptyAndSetSomeAttributes()
    {
        $obj = (new \BeAmado\OjsMigrator\Util\MemoryManager())->create();
        $obj->set('name', 'Bruce Dickinson');
        $obj->set('quality', 'Best singer ever');

        $this->assertSame(
            $obj->get('name')->getValue(),
            'Bruce Dickinson'
        );
    }

    public function testTwoLevelsArray()
    {
        $arr = array(
            'name' => 'Bernardo',
            'movie' => 'Lord of the rings',
            'colors' => array('green', 'blue', 'black'),
            'heroes' => array(
                'avengers' => array(
                    'Iron Man',
                    'Hulk',
                    'Thor',
                    'Captain America',
                ),
            ),
        );

        $arrObj = new MyObject($arr);

        $this->assertInstanceOf(
            MyObject::class,
            $arrObj->get('colors')
        );
    }

    public function testArrayIntoObject()
    {
        $arr = array(
            'name' => 'Bernardo',
            'movie' => 'Lord of the rings',
            'colors' => array('green', 'blue', 'black'),
            'heroes' => array(
                'avengers' => array(
                    'Iron Man',
                    'Hulk',
                    'Thor',
                    'Captain America',
                ),
            ),
        );

        $arrObj = new MyObject($arr);

        $this->assertEquals(
            $arr,
            array(
                'name' => $arrObj->get('name')->getValue(),
                'movie' => $arrObj->get('movie')->getValue(),
                'colors' => array(
                    $arrObj->get('colors')->get(0)->getValue(),
                    $arrObj->get('colors')->get(1)->getValue(),
                    $arrObj->get('colors')->get(2)->getValue(),
                ),
                'heroes' => array(
                    'avengers' => array(
                        $arrObj->get('heroes')->get('avengers')->get(0)->getValue(),
                        $arrObj->get('heroes')->get('avengers')->get(1)->getValue(),
                        $arrObj->get('heroes')->get('avengers')->get(2)->getValue(),
                        $arrObj->get('heroes')->get('avengers')->get(3)->getValue(),
                    ),
                ),
            )
        );
    }

}
