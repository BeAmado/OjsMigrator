<?php

use PHPUnit\Framework\TestCase;

class AppTest extends TestCase
{
    public function testFoundClassMongo()
    {
        $this->assertInstanceOf(
            '\BeAmado\OjsMigrator\Entity\Mongo',
            new \BeAmado\OjsMigrator\Entity\Mongo()
        );
    }

    public function testFoundClassMongoose()
    {
        $this->assertInstanceOf(
            '\BeAmado\OjsMigrator\Mongoose',
            new \BeAmado\OjsMigrator\Mongoose()
        );
    }

}
