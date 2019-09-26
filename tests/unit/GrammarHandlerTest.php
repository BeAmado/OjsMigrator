<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Util\GrammarHandler;
use BeAmado\OjsMigrator\StubInterface;

class GrammarHandlerTest extends TestCase implements StubInterface
{
    public function getStub()
    {
        return new class extends GrammarHandler {
            use BeAmado\OjsMigrator\TestStub;
        };
    }

    public function testGetPluralOfDog()
    {
        $this->assertEquals(
            'dogs',
            (new GrammarHandler())->getPlural('dog')
        );
    }

    public function testGetPluralOfFairy()
    {
        $this->assertEquals(
            'fairies',
            (new GrammarHandler())->getPlural('fairy')
        );
    }

    public function testGetSingleOfHurricanes()
    {
        $this->assertEquals(
            'Hurricane',
            (new GrammarHandler())->getSingle('Hurricanes')
        );
    }

    public function testGetSingularOfQueries()
    {
        $this->assertEquals(
            'Query',
            (new GrammarHandler())->getSingle('Queries')
        );
    }
} 
