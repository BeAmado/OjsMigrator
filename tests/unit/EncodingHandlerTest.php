<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Util\EncodingHandler;

class EncodingHandlerTest extends TestCase
{
    public function setUp() : void
    {
        $this->badChars = array(
            'Formul&Atilde;&iexcl;rio de Avalia&Atilde;&sect;&Atilde;&pound;o',
            'te&Atilde;&sup3;rico-cr&Atilde;&shy;tico',
        );

        $this->goodChars = array(
            'Formulário de Avaliação',
            'teórico-crítico',
        );
    }

    public function testStringWithGoodChars()
    {
        $str = "Ça ira. Quelque jour j'habiterai au Canada.";
        $this->assertSame(
            $str,
            (new EncodingHandler())->fixEncoding($str)
        );
    }

    public function testHtmlEntitiesStringWithGoodEncoding()
    {
        $str = \htmlentities('Strange déjà vu');
        $this->assertSame(
            $str,
            (new EncodingHandler())->fixHtmlEntityEncoding($str)
        );
    }

    public function testFixEncodingOfFirstTestString()
    {
        $bad = html_entity_decode($this->badChars[0]);
        $good = $this->goodChars[0];

        $this->assertSame(
            $good,
            (new EncodingHandler())->fixEncoding($bad)
        );
    }

    public function testFixHtmlEncodingOfSecondTestString()
    {
        $bad = $this->badChars[1];
        $good = htmlentities($this->goodChars[1]);

        $this->assertSame(
            $good,
            (new EncodingHandler())->fixHtmlEntityEncoding($bad)
        );
    }

    public function testFixHtmlEntitiesEncodingOfAllTestStrings()
    {
        $result = true;
        $eh = new EncodingHandler();

        for ($i = 0; $i < count($this->badChars); $i++) {
            $result = $result && (
                $eh->fixHtmlEntityEncoding($this->badChars[$i]) ===
                htmlentities($this->goodChars[$i])
            );
        }

        $this->assertTrue($result);
    }

    public function testFixEncodingOfAllTestStrings()
    {
        $result = true;
        $eh = new EncodingHandler();

        for ($i = 0; $i < count($this->badChars); $i++) {
            $result = $result && (
                $eh->fixEncoding(html_entity_decode($this->badChars[$i])) ===
                $this->goodChars[$i]
            );
        }

        $this->assertTrue($result);
    }
}
