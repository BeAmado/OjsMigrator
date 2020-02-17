<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Registry;
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
        $tkNow = Registry::get('TimeKeeper')->now();

        $arr = explode(' ', microtime());

        $now = round(
            ( ((float) $arr[0]) + ((float) $arr[1]) ) * 1000 // miliseconds
        );

        $this->assertTrue(abs($now - $tkNow) <= 2); // 2 miliseconds of tolerance
    }

    public function testGetElapsedTimeForA100MilisecondAwait()
    {
        $begin = Registry::get('TimeKeeper')->now();

        usleep(100000); // 1.0e5 microseconds

        $elapsed = Registry::get('TimeKeeper')->elapsedTime($begin);

        $this->assertTrue(
            $elapsed >= 98 && $elapsed <= 102
        );
    }

    public function testWaitFor50Miliseconds()
    {
        $begin = Registry::get('TimeKeeper')->now();
        Registry::get('TimeKeeper')->wait(50);
        $end = Registry::get('TimeKeeper')->now();

        $elapsed = $end - $begin;

        $this->assertTrue($elapsed >= 48 && $elapsed <= 52);
    }
}
