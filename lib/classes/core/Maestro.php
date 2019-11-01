<?php

namespace BeAmado\OjsMigrator;

class Maestro
{
    protected static function is($name, $x)
    {
        if (!\is_string($name) || !\is_string($x))
            return false;

        return \strtolower(\substr($name, -strlen($x))) === \strtolower($x);
    }

    protected static function isManager($name)
    {
        return self::is($name, 'manager');
    }

    protected static function isHandler($name)
    {
        return self::is($name, 'handler');
    }

    protected static function isDirectory($name)
    {
        return self::is($name, 'dir') || self::is($name, 'directory');
    }

    protected static function isDao($name)
    {
        return self::is($name, 'dao');
    }

    protected static function getDefaultDir($name)
    {
        if (!self::isDirectory($name))
            return;

        if (\strtolower($name) === 'ojsdir')
            return Registry::get('FileSystemManager')->parentDir(BASE_DIR);

        if (\strtolower($name) === 'schemadir')
            return Registry::get('FileSystemManager')->formPathFromBaseDir(
                'schema'
            );
    }

    protected static function getDao($name)
    {
        /** @var $tablesNames array*/
        $tablesNames = Registry::get('SchemaHandler')->getTablesNames();
        
        $index = \array_search(
            Registry::get('CaseHandler')->transformCaseTo(
                'lower', 
                self::isDao($name) ? \substr($name, 0, -3) : $name
            ),
            \array_map(function($tableName) {
                return Registry::get('CaseHandler')->transformCaseTo(
                    'lower',
                    $tableName
                );
            }, $tablesNames)
        );

        if ($index !== false)
            return Factory::create('DAO', $tableNames[$index]);
    }

    /**
     *
     *
     * @param string $name
     * @return mixed
     */
    public static function get($name)
    {
        if (Registry::hasKey($name))
            return Registry::get($name);

        if (self::isManager($name) || self::isHandler($name))
            return (new Factory())->create($name);

        if (self::isDirectory($name))
            return self::getDefaultDir($name);

        if (self::isDao($name)) {
            Registry::set($name, self::getDao($name));

            if (\is_a(Registry::get($name, \BeAmado\OjsMigrator\Db\DAO)))
                return Registry::get($name);
            else
                Registry::remove($name);
        }
    }

    /**
     * Sets the directory where the ojs files are. If no argument is passed, 
     * the directory will be set as the parent directory of the OjsMigrator.
     *
     * @param string $dir
     * @return void
     */
    public static function setOjsDir($dir = null)
    {
        if (
            $dir !== null && 
            !Registry::get('FileSystemManager')->dirExists($dir)
        ) {
            return;
        }

        Registry::set(
            'OjsDir',
            $dir ?: self::getDefaultDir('OjsDir')
        );
    }

    /**
     * Sets the directory that will store the .json files of the OJS schema.
     *
     * @param string $dir
     * @return void
     */
    public static function setSchemaDir($dir = null)
    {
        if (
            $dir !== null && 
            !Registry::get('FileSystemManager')->dirExists($dir)
        ) {
            return;
        }

        Registry::set(
            'SchemaDir',
            $dir ?: self::getDefaultDir('SchemaDir')
        );
    }
}
