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

    protected static function isStatement($name)
    {
        return self::is($name, 'stmt') || self::is($name, 'statement');
    }

    protected static function isMapper($name)
    {
        return self::is($name, 'mapper');
    }

    protected static function isKeeper($name)
    {
        return self::is($name, 'keeper');
    }

    protected static function getDefaultDir($name)
    {
        if (!self::isDirectory($name))
            return;

        if (\strtolower(\substr($name, -9)) === 'directory')
            $name = \substr($name, 0, -6);

        if (\strtolower($name) === 'ojsdir')
            return Registry::get('FileSystemManager')->parentDir(BASE_DIR);

        if (\strtolower($name) === 'schemadir')
            return Registry::get('FileSystemManager')->formPathFromBaseDir(
                'schema'
            );
        
        if (\strtolower($name) === 'entitiesdir')
            return Registry::get('FileSystemManager')->formPathFromBaseDir(
                'entities'
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
            return (new Factory())->create('DAO', $tablesNames[$index]);
    }

    protected static function getStatement($name)
    {
        /** @var $stmtName string */
        $stmtName = null;

        if (\strtolower(\substr($name, -4)) === 'stmt')
            $stmtName = \substr($name, 0, -4);
        elseif (\strtolower(\substr($name, -9)) === 'statement')
            $stmtName = \substr($name, 0, -9);
        else
            $stmtName = $stmt;

        return Registry::get('StatementHandler')->getStatement($stmtName);
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

        if (
            self::isHandler($name) || 
            self::isKeeper($name) ||
            self::isManager($name) || 
            self::isMapper($name)
        )
            return (new Factory())->create($name);

        if (self::isDirectory($name))
            return self::getDefaultDir($name);

        if (self::isDao($name)) {
            Registry::set($name, self::getDao($name));

            if (\is_a(Registry::get($name), \BeAmado\OjsMigrator\Db\DAO::class))
                return Registry::get($name);
            else
                Registry::remove($name);
        }

        if (self::isStatement($name))
            return self::getStatement($name);
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
