<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Util\GrammarHandler;

// interfaces
use BeAmado\OjsMigrator\Test\StubInterface;

// traits
use BeAmado\OjsMigrator\Test\TestStub;

class GrammarHandlerTest extends TestCase implements StubInterface
{
    public function getStub()
    {
        return new class extends GrammarHandler {
            use TestStub;
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
