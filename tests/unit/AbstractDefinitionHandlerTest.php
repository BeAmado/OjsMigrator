<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Db\AbstractDefinitionHandler;
use BeAmado\OjsMigrator\Registry;

// traits
use BeAmado\OjsMigrator\TestStub;

// interfaces
use BeAmado\OjsMigrator\StubInterface;

class AbstractDefinitionHandlerTest extends TestCase implements StubInterface
{
    public function getStub()
    {
        return new class extends AbstractDefinitionHandler {
            use TestStub;
        };
    }

    public function testCheckThatObjectIsMaori()
    {
        $o = Registry::get('MemoryManager')->create(array(
            'name' => 'Maori',
            'text' => 'Tenei te tanga ta',
            'attributes' => array(
                'attitute' => 'very brave',
                'quality' => 'skilled as heck',
            ),
            'children' => array(),
        ));

        $this->assertTrue(
            $this->getStub()->callMethod(
                'nameIs',
                array(
                    'o' => $o, 
                    'name' => 'maori',
                )
            )
        );
    }
}
