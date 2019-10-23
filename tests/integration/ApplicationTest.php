<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Application;
use BeAmado\OjsMigrator\Registry;

///////// interfaces /////////////////
use BeAmado\OjsMigrator\StubInterface;

/////////// traits ///////////////////
use BeAmado\OjsMigrator\TestStub;
use BeAmado\OjsMigrator\WorkWithFiles;

class ApplicationTest extends TestCase implements StubInterface
{
    use WorkWithFiles;

    public static function tearDownAfterClass() : void
    {
        Registry::get('SchemaHandler')->removeSchemaDir();

        $sandbox = Registry::get('FileSystemManager')->formPathFromBaseDir(
            array(
                'tests',
                '_data',
                'sandbox',
            )
        );

        Registry::get('FileSystemManager')->removeWholeDir($sandbox);
    }

    public function getStub()
    {
        return new class extends Application {
            use TestStub;
        };
    }
}
