<?php

namespace BeAmado\OjsMigrator;
use \BeAmado\OjsMigrator\Util\MemoryManager;

class Registry
{
    /**
     * @var array
     */
    private static $data = array();

    public static function hasKey($name)
    {
        return \array_key_exists(\strtolower($name), self::$data);
    }

    public static function get($key)
    {
/*'      return (self::hasKey($key)
            ? self::$data[\strtolower($key)] 
            : Maestro::get($key));
            */
            if (!self::hasKey($key))
                self::set($key, Maestro::get($key));

            if (self::$data[\strtolower($key)] === null)
                self::remove($key);
            else
                return self::$data[\strtolower($key)];
    }

    public static function set($key, $value)
    {
        self::$data[\strtolower($key)] = $value;
    }

    public static function remove($key)
    {
        if (!self::hasKey($key)) {
            return;
        }

        (new MemoryManager())->destroy(self::$data[\strtolower($key)]);
        unset(self::$data[\strtolower($key)]);
    }

    public static function clear()
    {
        foreach (\array_keys(self::$data) as $key) {
            self::remove($key);
        }
        unset($key);
    }

    public static function countKeys()
    {
        return \count(self::$data);
    }

    public static function increment($key)
    {
        if (
            !self::hasKey($key) || //does not have the key
            !\is_numeric(self::get($key)) || // is not a number
            \strpos('' . self::get($key), '.') !== false // is a float
        ) {
            return;
        }

        self::$data[\strtolower($key)]++;
    }
}
