<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\MyObject;

// interfaces
use BeAmado\OjsMigrator\Test\StubInterface;

// traits
use BeAmado\OjsMigrator\Test\TestStub;

class MyObjectTest extends TestCase implements StubInterface
{
    public function getStub()
    {
        return new class extends MyObject {
            use TestStub;
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
    
    public function testGetLastElement()
    {
        $obj = new MyObject([1, 2, 3, 4, 5]);

        $this->assertTrue(
            $obj->get(-5)->getValue() === 1 &&
            $obj->get(-2)->getValue() === 4 &&
            $obj->get(-1)->getValue() === 5
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

    public function testObjectHasAttributeAge()
    {
        $obj = new MyObject();
        $obj->set('age', 27);
        $obj->set('color', 'green');

        $this->assertTrue($obj->hasAttribute('age'));
    }
    
    public function testObjectDoesNotHaveAttibuteWeight()
    {
        $obj = new MyObject(array(
            'age' => 45,
            'hero' => 'Iron Man',
        ));

        $this->assertFalse($obj->hasAttribute('weight'));
    }

    public function testCanCreateInstancePassingAnotherMyObjectInstance()
    {
        $obj1 = new MyObject(array(
            'singer' => 'Bruce Dickinson',
            'bass' => 'Flea',
            'guitar' => 'Reb Beach',
            'drums' => 'Mike Portnoy',
        ));

        $obj2 = new MyObject($obj1);

        $this->assertTrue(
            $obj2->get('singer')->getValue() === 'Bruce Dickinson' &&
            $obj2->get('bass')->getValue() === 'Flea' &&
            $obj2->get('guitar')->getValue() === 'Reb Beach' &&
            $obj2->get('drums')->getValue() === 'Mike Portnoy'
        );
    }

    public function testPushElements()
    {
        $obj = new MyObject();

        $obj->push('Iron Maiden');
        $obj->push('Helloween');
        $obj->push(new MyObject(array(
            'bands' => array(
                'Gamma Ray', 
                'Whitesnake'
            ),
        )));

        $this->assertEquals(
            array(
                'Iron Maiden',
                'Helloween',
                array(
                    'bands' => array(
                        'Gamma Ray',
                        'Whitesnake',
                    ),
                ),
            ),
            $obj->toArray()
        );
    }
}
