<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Util\GrammarHandler;

class GrammarHandlerTest extends TestCase
{
    private function getStub()
    {
        require_once(dirname(__FILE__) . '/../TestStub.php');
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
