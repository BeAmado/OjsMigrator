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
        return \array_key_exists($name, self::$data);
    }

    public static function get($key)
    {
        if (self::hasKey($key)) {
            return self::$data[$key];
        }
    }

    public static function set($key, $value)
    {
        self::$data[$key] = $value;
    }

    public static function remove($key)
    {
        if (!self::hasKey($key)) {
            return;
        }

        (new MemoryManager())->destroy(self::$data[$key]);
        unset(self::$data[$key]);
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
            !\is_numeric(self::$data[$key]) || // is not a number
            \strpos('' . self::$data[$key], '.') !== false // is a float
        ) {
            return;
        }

        self::$data[$key]++;
    }
}
