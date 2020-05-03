<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Util\SerialDataHandler;
use BeAmado\OjsMigrator\Registry;
use BeAmado\OjsMigrator\Test\StubInterface;
use BeAmado\OjsMigrator\Test\TestStub;

class SerialDataHandlerTest extends TestCase implements StubInterface
{
    public function getStub()
    {
        return new class extends SerialDataHandler { use TestStub; };
    }

    protected function handler()
    {
        return Registry::get('SerialDataHandler');
    }

    protected function areEqual($v1, $v2)
    {
        return $v1 == $v2;
    }

    protected function areStrictlyEqual($v1, $v2)
    {
        return $v1 === $v2;
    }

    public function testCanSeeIfTheSerializedDataIsOk()
    {
        $serializedOk = serialize([
            'band' => 'Rush',
            'song' => 'Time Stand Still',
        ]);
        $serializedNok = 'a:2:{s:7:"content";s:3:"can take it";}';
        $anything = 'Tom Sawyer';

        $this->assertSame(
            '1-0-0',
            implode('-', array_map(function($el) {
                return (int) $this->handler()->serializationIsOk($el);
            }, [$serializedOk, $serializedNok, $anything]))
        );
    }

    public function testCanSerializeANullValue()
    {
        $this->assertSame(
            '1-1',
            implode('-', array_map(function($el) { return $el === 'N;'; }, [
                $this->getStub()->callMethod('nullRepr'),
                (new SerialDataHandler())->manuallySerialize(null),
            ]))
        );
    }

    public function testCanSerializeABooleanValue()
    {
        $this->assertSame(
            '1-1-1-1',
            implode('-', array_merge(
                array_map(function($el) { return $el === 'b:1;'; }, [
                    $this->getStub()->callMethod('boolRepr', true),
                    $this->handler()->manuallySerialize(true),
                ]),
                array_map(function($el) { return $el === 'b:0;'; }, [
                    $this->getStub()->callMethod('boolRepr', false),
                    $this->handler()->manuallySerialize(false),
                ])
            ))
        );
    }

    public function testCanSerializeAnIntegerValue()
    {
        $this->assertSame(
            '1-1-1-1',
            implode('-', array_map(function($el) { return (int) $el; }, [
                $this->getStub()->callMethod('intRepr', 34) === 'i:34;',
                $this->handler()->manuallySerialize(-57) === 'i:-57;',
                $this->getStub()->callMethod('intRepr', 'ds') === 'i:ds;',
                $this->handler()->manuallySerialize(0) === 'i:0;',
            ]))
        );
    }

    public function testCanSerializeADoubleValue()
    {
        $this->assertSame(
            '1-1-1-1-1-1',
            implode('-', array_map(function($el) { return (int) $el; }, [
                $this->getStub()->callMethod(
                    'doubleRepr',
                    34.78) === 'd:34.78;',
                $this->handler()->manuallySerialize(-57.33) === 'd:-57.33;',
                $this->getStub()->callMethod(
                    'doubleRepr',
                    0.000) === 'd:0;',
                $this->handler()->manuallySerialize(0) === 'i:0;',
                $this->handler()->manuallySerialize(0.0) === 'd:0;',
                $this->handler()->manuallySerialize(0.0546) === 'd:0.0546;',
            ]))
        );
    }

    public function testCanSerializeAStringValue()
    {
        $this->assertSame(
            '1-1-1-1-1',
            implode('-', [
                (int) $this->areStrictlyEqual(
                    $this->getStub()->callMethod('stringRepr', 'Mamma mia'),
                    's:9:"Mamma mia";'
                ),
                (int) $this->areStrictlyEqual(
                    $this->getStub()->callMethod(
                        'stringRepr',
                        "Gesù c'è la luce per tutti"
                    ),
                    's:28:"Gesù c\'è la luce per tutti";'
                ),
                (int) $this->areStrictlyEqual(
                    $this->getStub()->callMethod('stringRepr', '4'),
                    's:1:"4";'
                ),
                (int) $this->areStrictlyEqual(
                    $this->handler()->manuallySerialize('Nothing to say'),
                    's:14:"Nothing to say";'
                ),
                (int) $this->areStrictlyEqual(
                    $this->handler()->manuallySerialize('Mamãe é demais!'),
                    's:17:"Mamãe é demais!";'
                )
            ])
        );
    }

    public function testCanSerializeAnArray()
    {
        $this->assertSame(
            '1-1',
            implode('-', [
                (int) $this->areStrictlyEqual(
                    $this->getStub()->callMethod('arrayRepr', [
                        'data' => [
                            'prenom' => 'Bruce',
                            'nom' => 'Wayne',
                        ]
                    ]),
                    'a:2:{s:6:"prenom";s:5:"Bruce";s:3:"nom";s:5:"Wayne";}'
                ),
                (int) $this->areStrictlyEqual(
                    $this->handler()->manuallySerialize([
                        'name',
                        'age',
                        35,
                    ]),
                    'a:3:{i:0;s:4:"name";i:1;s:3:"age";i:2;i:35;}'
                ),
            ])
        );
    }
}
