<?php

namespace BeAmado\OjsMigrator;

class TestRegistry extends Registry
{
    private static $data = array();
    
    public static function create($name)
    {
        if ($name === 'OjsScenarioTester')
            self::$data[$name] = new OjsScenarioTester();
    }

    public static function get($name)
    {
        if (!self::hasKey($name))
            self::create($name);


        if (self::hasKey($name))
            echo "\n\n\n\nBling blog\n\n\n\n\n\n\n\n\n";
            return self::$data[$name];

        echo "\n\n\n\n\n\nn\n\n\bbbbbbbbbbbbbbbbbbbbbbbabbbbbbbbbbbbbBBBBBBBBBBBBBBBBBBBBBB\n\n\n\n\n";
        return Registry::get($name);
    }
}
