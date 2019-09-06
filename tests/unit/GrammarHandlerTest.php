<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Util\GrammarHandler;

class GrammarHandlerTest extends TestCase
{
    protected function setUp() : void
    {
        require_once(BeAmado\OjsMigrator\LIB_DIR . '/classes/util/GrammarHandler.php');
	require_once(dirname(__FILE__) . '/../TestStub.php');
        $this->ghStub = new class extends GrammarHandler {
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
