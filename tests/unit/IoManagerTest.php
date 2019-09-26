<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Util\IoManager;
use BeAmado\OjsMigrator\StubInterface;

class IoManagerTest extends TestCase implements StubInterface
{

    public function getStub()
    {
        return new class extends IoManager {
            use BeAmado\OjsMigrator\TestStub;
        };
    }

    public function testCanInstantiate()
    {
        $this->assertInstanceOf(
            IoManager::class,
            (new IoManager())
        );
    }

    public function testCanOpenStandardInput()
    {
        $io = $this->getStub();
        $io->callMethod('openStream', 'stdin');
        $this->assertIsResource($io->callMethod('getStream', 'stdin'));
    }

    public function testCanCloseStandardInput()
    {
        $io = $this->getStub();
        $io->callMethod('openStream', 'stdin');
        $io->callMethod('closeStream', 'stdin');
        $this->assertFalse(is_resource($io->callMethod('getStream', 'stdin')));
    }

    /*public function testGetUserInput()
    {
        $input = 100;
        $begin = \time();
        $input = (New IoManager())->getUserInput(5);
        $end = \time();

        $timelapse = $end - $begin;

        if ($timelapse >= 5) {
            $this->assertSame(
                null,
                $input
            );
        } else {
            $this->assertIsString($input);
        }
    }*/

}
