<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Util\TimeKeeper;
use BeAmado\OjsMigrator\StubInterface;
use BeAmado\OjsMigrator\TestStub;

class TimeKeeperTest extends TestCase implements StubInterface
{
    public function getStub()
    {
        return new class extends TimeKeeper {
            use TestStub;
        };
    }

    public function testGetNow()
    {
        $tkNow = (new TimeKeeper())->now();

        $arr = explode(' ', microtime());

        $now = $arr[0] + $arr[1];

        $this->assertTrue(abs($now - $tkNow) < 0.1);
    }

    public function testGetElapsedTimeForAThreeSecondAwait()
    {
        $this->markTestSkipped('Skip this test in order for the unit tests to '
        . 'run fast');
        $begin = (new TimeKeeper())->now();

        do {
            // just waiting
            $now = (new TimeKeeper())->now();
        } while (($now - $begin) < 3);

        $elapsed = (new TimeKeeper())->elapsedTime($begin);

        $this->assertTrue(
            $elapsed > 3 and $elapsed < 3.01
        );
    }

    public function testWaitForTwoSeconds()
    {
        $this->markTestSkipped('Skip this test in order for the unit tests to '
        . 'run fast');
        $begin = (new TimeKeeper())->now();
        (new TimeKeeper())->wait(2000);
        $end = (new TimeKeeper())->now();

        $elapsed = $end - $begin;

        $this->assertTrue($elapsed > 1.99 && $elapsed < 2.01);
    }
}
