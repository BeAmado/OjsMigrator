<?php

namespace BeAmado\OjsMigrator\Test;
use \BeAmado\OjsMigrator\Registry;

use \PHPUnit\Framework\TestCase;

abstract class FunctionalTest extends TestCase
{
    public static function setUpBeforeClass($args = array(
        'clearRegistry' => true,
    )) : void {
        if (!\array_key_exists('clearRegistry', $args))
            $args['clearRegistry'] = true;

        if ($args['clearRegistry'])
            Registry::clear();
        (new OjsScenarioHandler())->setUpStage($args);
    }

    public static function tearDownAfterClass($args = array()) : void
    {
        (new OjsScenarioHandler())->tearDownStage($args);
        Registry::clear();
    }

    protected function areEqual($a, $b)
    {
        return $a == $b;
    }
}
