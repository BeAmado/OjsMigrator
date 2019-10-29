<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Registry;

class ArrayHandlerTest extends TestCase
{
    public function testUnionBetween2Arrays()
    {
        $arr1 = array(1, 2, 3);
        $arr2 = array(7, 8, 9);

        $expected = array(1, 2, 3, 7, 8, 9);
        $result = Registry::get('ArrayHandler')->union($arr1, $arr2);

        $this->assertEquals(
            $expected,
            $result
        );
    }

    public function testUnionBetween2ArraysWithRepeatedValues()
    {
        $arr1 = array(1, 1, 2, 3, 3, 2, 5);
        $arr2 = array(1, 2, 9, 78, 4, 5, 6, 3);

        $expected = array(1, 2, 3, 4, 5, 6, 78, 9);
        $result = Registry::get('ArrayHandler')->union($arr1, $arr2);

        sort($expected);
        sort($result);

        $this->assertEquals(
            $expected,
            $result
        );
    }

    public function testUnionBetweenMoreThan2Arrays()
    {
        $arrays = array(
            array(1, 2, 3, 4),
            5, 
            array(9, 78, 23, 5, 1, 2, 7),
            'Vakatawa'
        );

        $expected = array(1, 2, 3, 4, 5, 9, 78, 23, 7, 'Vakatawa');
        sort($expected);

        $result = Registry::get('ArrayHandler')->unionN($arrays);
        sort($result);

        $this->assertEquals($expected, $result);
    }
}
