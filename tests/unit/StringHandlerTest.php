<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Util\StringHandler;

class StringHandlerTest extends TestCase
{
    public function testHasHtmlEntity()
    {
        $str = \htmlentities("Je pensait qu'avait déjà tout vu dans ce monde!");
        $this->assertTrue((new StringHandler())->hasHtmlEntity($str));
    }

    public function testDoesNotHaveHtmlEntity()
    {
        $str = 'One big world away!';
        $this->assertFalse((new StringHandler())->hasHtmlEntity($str));
    }

    public function testEncodeSpecialChars()
    {
        $str = 'Les chiens sont des animaux très speciales. '
            . 'Ils sont drôles, très affectueux et nous font heureux.';

        $this->assertSame(
            \htmlentities($str),
            (new StringHandler())->encodeSpecialChars($str)
        );
    }

    public function testDecodeSpecialChars()
    {
        $str = "Je suis allé chez ma mère aujourd'hui.";
        $encoded = (new StringHandler())->encodeSpecialChars($str);

        $this->assertSame(
            $str,
            (new StringHandler())->decodeSpecialChars($encoded)
        );
    }
}
