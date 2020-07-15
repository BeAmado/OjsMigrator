<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Util\EncodingHandler;
use BeAmado\OjsMigrator\Registry;
use BeAmado\OjsMigrator\Test\TestStub;
use BeAmado\OjsMigrator\Test\StubInterface;

class EncodingHandlerTest extends TestCase implements StubInterface
{
    public function getStub()
    {
        return new class extends EncodingHandler {
            use TestStub;
        };
    }

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

    public function testTheBrokenCharsArrayMatchesTheFixedOnes()
    {
        $broken = $this->getStub()->callMethod('getBrokenChars');
        $fixed = $this->getStub()->callMethod('getFixedChars');
        $charMapping = $this->getStub()->callMethod('getCharMapping');

        $this->assertSame(
            '1-1',
            implode('-', [
                (int) (
                    (count($broken) === count($charMapping)) &&
                    (count($fixed) === count($charMapping))
                ),
                (int) array_reduce(
                    range(0, count($charMapping) - 1),
                    function($carry, $i) use ($broken, $fixed, $charMapping) {
                        return $carry && (
                            ($broken[$i] === $charMapping[$i]['broken']) &&
                            ($fixed[$i] === $charMapping[$i]['fixed'])
                        );
                    },
                    true
                ),
            ])
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
            Registry::get('EncodingHandler')->fixEncoding($bad)
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
