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
            '1-1-1',
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
                (int) $this->areStrictlyEqual(
                    $this->handler()->manuallySerialize([
                        'users' => [
                            [
                                'name' => 'John',
                                'age' => 22,
                            ],
                            [
                                'name' => 'Mary',
                                'age' => 26,
                            ],
                        ]
                    ]),
                    'a:1:{'
                  .     's:5:"users";a:2:{'
                  .         'i:0;a:2:{'
                  .             's:4:"name";s:4:"John";'
                  .             's:3:"age";i:22;'
                  .         '}'
                  .         'i:1;a:2:{'
                  .             's:4:"name";s:4:"Mary";'
                  .             's:3:"age";i:26;'
                  .         '}'
                  .     '}'
                  . '}'
                ),
            ])
        );
    }

    public function testCanGetTheSerializedType()
    {
        $this->assertSame(
            '-null-bool-int-double-string-array-',
            implode('-', array_map(function($str) {
                return $this->getStub()->callMethod('getSerializedType', $str);
            }, [
                12,
                'N;',
                'b:1;',
                'i:-90;',
                'd:0.8937;',
                's:6:"Amaral";',
                'a:2:{s:4:"name";s:6:"Amaral";}',
                'lkan',
            ]))
        );
    }

    public function testCanUnserializeANullValue()
    {
        $this->assertNull($this->handler()->manuallyUnserialize('N;'));
    }

    public function testCanUnserializeABooleanValue()
    {
        $this->assertSame(
            '1-1',
            implode('-', [
                (int) $this->areStrictlyEqual(
                    true,
                    $this->handler()->manuallyUnserialize('b:1;')
                ),
                (int) $this->areStrictlyEqual(
                    false,
                    $this->handler()->manuallyUnserialize('b:0;')
                ),
            ])
        );
    }

    public function testCanUnserializeAnIntegerValue()
    {
        $this->assertSame(
            '23;-363;11;-89;',
            implode(';', array_merge(
                array_map(function($item) {
                    return $this->getStub()->callMethod(
                        'unserializeInteger',
                        $item
                    );
                }, [
                    'i:23;',
                    'i:-363;',
                ]),
                array_map(function($item) {
                    return $this->handler()->manuallyUnserialize($item);
                }, [
                    'i:11;',
                    'i:-89;',
                    'afe',
                ])
            ))
        );
    }

    public function testCanUnserializeADoubleValue()
    {
        $this->assertSame(
            '1;1',
            implode(';', [
                (int) $this->areStrictlyEqual(
                    -89.3,
                    $this->getStub()->callMethod(
                        'unserializeDouble',
                        'd:-89.3;'
                    )
                ),
                (int) $this->areStrictlyEqual(
                    66.3,
                    $this->handler()->manuallyUnserialize('d:66.3;')
                ),
            ])
        );
    }

    public function testCanUnserializeAStringValue()
    {
        $this->assertSame(
            'Amaral-Hannah-Mamãe-Gesù',
            implode('-', array_map(function($item) {
                return $this->handler()->manuallyUnserialize($item);
            }, [
                's:6:"Amaral";',
                's:2:"Hannah";',
                's:5:"Mamãe";',
                's:5:"Gesù";',
            ]))
        );
    }

    public function testCanUnserializeAnArray()
    {
        $ah = Registry::get('ArrayHandler');
        $this->assertSame(
            '1-1',
            implode('-', [
                (int) $ah->equals(
                    ['Amaral', 'Hannah', 30],
                    $this->getStub()->callMethod(
                        'unserializeArray',
                        'a:3:{i:0;s:6:"Amaral";i:1:;s:6:"Hannah";i:2;i:30;}'
                    )
                ),
                (int) $ah->areEquivalent(
                    [
                        ['name' => 'Amaral', 'age' => 12],
                        ['name' => 'Hannah', 'age' => 13],
                    ],
                    $this->getStub()->callMethod(
                        'unserializeArray',
                        'a:2:{'
                      .     'i:0;a:2:{'
                      .         's:4:"name";s:6:"Amaral";'
                      .         's:3:"age";i:12;'
                      .     '}'
                      .     'i:1;a:2:{'
                      .         's:4:"name";s:6:"Hannah";'
                      .         's:3:"age";i:13;'
                      .     '}'
                      . '}'
                    )
                ),
            ])
        );
    }

    public function testCanGetTheBordersOfASerializedString()
    {
        $data = [
            'none' => 'b:1;',
            'pure' => 's:5:"mamma";',
            'array' => 'a:2:{i:0;s:10:"Angels Cry";i:1;s:9:"Holy Land";i:2;s:9:"Fireworks";}',
        ];

        $expectedIndexes = [
            'none' => null,
            'pure' => [0, 11],
            'array' => [
                [9, 26],
                [31, 46],
                [51, 66],
            ],
        ];

        $indexes = [
            'none' => $this->getStub()->callMethod(
                'getStringBorderIndexes',
                $data['none']
            ),
            'pure' => $this->getStub()->callMethod(
                'getStringBorderIndexes',
                $data['pure']
            ),
            'array' => [$this->getStub()->callMethod(
                'getStringBorderIndexes',
                $data['array']
            )],
        ];

        foreach ([0, 1] as $lastIndex) {
            $indexes['array'][] = $this->getStub()->callMethod(
                'getStringBorderIndexes',
                [
                    'data' => $data['array'],
                    'offset' => $indexes['array'][$lastIndex][1],
                ]
            );
        }

        $this->assertSame(
            '1-1-1',
            implode('-', [
                (int) $this->areStrictlyEqual(
                    $expectedIndexes['none'],
                    $indexes['none']
                ),
                (int) Registry::get('ArrayHandler')->equals(
                    $expectedIndexes['pure'],
                    $indexes['pure']
                ),
                (int) Registry::get('ArrayHandler')->areEquivalent(
                    $expectedIndexes['array'],
                    $indexes['array']
                ),
            ])
        );
    }
}
