<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Maestro;
use BeAmado\OjsMigrator\Registry;
use BeAmado\OjsMigrator\WorkWithFiles;

class MaestroTest extends TestCase
{
    use WorkWithFiles;

    public function testSetASpecifiedOjsDir()
    {
        $dir = Registry::get('FileSystemManager')
        Maestro::setOjsDir();
    }
}
