<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Util\RangeHandler;
use BeAmado\OjsMigrator\Registry;

class RangeHandlerTest extends TestCase
{
    public function testCanFormTheRangeFrom301To400()
    {
        $range = (new RangeHandler())->formRange(301, 400);
        $this->assertSame('301-400', $range);
    }

    public function testTensRangeOf3256Is3251To3260()
    {
        $range = Registry::get('RangeHandler')->powerOfTenRange(3256, 1);
        $this->assertSame('3251-3260', $range);
    }

    public function testHundredsRangeOf3256Is3201To3300()
    {
        $range = Registry::get('RangeHandler')->powerOfTenRange(3256, 2);
        $this->assertSame('3201-3300', $range);
    }

    public function testBaseRangeOf3256Is1To10000()
    {
        $baseRange = Registry::get('RangeHandler')->baseRange(3256);
        $this->assertSame('1-10000', $baseRange);
    }

    public function testCanMakeRangesStructureForNumber293716()
    {
        $rangeStruct = Registry::get('RangeHandler')->rangesStructure(293716);
        $expectedStruct = array(
            '1-1000000',
            '200001-300000',
            '290001-300000',
            '293001-294000',
            '293701-293800',
            '293711-293720',
            '293716',
        );

        $this->assertEquals($expectedStruct, $rangeStruct);
    }

    public function testCanMakeRangesStringForNumber293716()
    {
        $ranges = Registry::get('RangeHandler')->rangesString(293716);
        $expected = Registry::get('FileSystemManager')->formPath(array(
            '1-1000000',
            '200001-300000',
            '290001-300000',
            '293001-294000',
            '293701-293800',
            '293711-293720',
            '293716',
        ));

        $this->assertSame($expected, $ranges);
    }

    public function testCanSeeWhichRangeIsLarger()
    {
        $range1 = Registry::get('RangeHandler')->formRange(1, 1000);
        $range2 = Registry::get('RangeHandler')->formRange(1, 100000);

        $this->assertSame(
            $range2,
            Registry::get('RangeHandler')->largestRange($range1, $range2)
        );
    }

    public function testCanGetRangesDiff()
    {
        $range1 = Registry::get('RangeHandler')->formRange(1, 1000);
        $range2 = Registry::get('RangeHandler')->formRange(1, 100000);

        $expected = Registry::get('FileSystemManager')->formPath(array(
            '1-100000',
            '1-10000',
        ));

        $this->assertSame(
            $expected,
            Registry::get('RangeHandler')->rangesDiff($range1, $range2)
        );
    }
}
