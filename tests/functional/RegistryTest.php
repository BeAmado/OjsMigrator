<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Registry;

class RegistryTest extends TestCase
{
    public function testCanInsertElementInTheRegistry()
    {
        $amountBefore = Registry::countKeys();
        Registry::set('animal', 'dog');
        $this->assertSame(
            $amountBefore + 1,
            Registry::countKeys()
        );
    }

    /**
     * @depends testCanInsertElementInTheRegistry
     */
    public function testCanRetrieveElementInTheRegistry()
    {
        $this->assertSame(
            'dog',
            Registry::get('animal')
        );
    }

    public function testClearRegistry()
    {
        Registry::set('color', 'green');
        Registry::set('band', 'Helloween');
        Registry::clear();
        $this->assertSame(
            0,
            Registry::countKeys()
        );
    }
}
