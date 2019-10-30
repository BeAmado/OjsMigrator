<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Util\CaseHandler;
use BeAmado\OjsMigrator\Registry;

// traits
use BeAmado\OjsMigrator\TestStub;

// interfaces
use BeAmado\OjsMigrator\StubInterface;

class CaseHandlerTest extends TestCase implements StubInterface
{
    public function getStub()
    {
        return new class extends CaseHandler {
            use TestStub;
        };
    }

    public function testGetSnakeCaseNameAsSnake()
    {
        $case = $this->getStub()->callMethod('getCaseName', 'snake_case');
        $this->assertSame('snake', $case);
    }

    public function testGetPascalCaseNameAsPascal()
    {
        $case = $this->getStub()->callMethod('getCaseName', 'PascalCase');
        $this->assertSame('pascal', $case);
    }

    public function testGetCamelCaseNameAsCamel()
    {
        $case = $this->getStub()->callMethod('getCaseName', 'camelCase');
        $this->assertSame('camel', $case);
    }

    public function testGetUppercaseNameAsUpper()
    {
        $case = $this->getStub()->callMethod('getCaseName', 'UPPERCASE');
        $this->assertSame('upper', $case);
    }

    public function testGetLowercaseNameAsLower()
    {
        $case = $this->getStub()->callMethod('getCaseName', 'lowercase');
        $this->assertSame('lower', $case);
    }

    public function testGetGeneralNameAsGeneral()
    {
        $case = $this->getStub()->callMethod('getCaseName', 'general');
        $this->assertSame('general', $case);
    }

    public function testTransformSnakeCaseToPascalCase()
    {
        $str = Registry::get('CaseHandler')->transformCaseFromTo(
            'snake_case',
            'PascalCase',
            'controlled_vocab_entries'
        );

        $this->assertSame(
            'ControlledVocabEntries',
            $str
        );
    }

    public function testTransformSnakeCaseToCamelCase()
    {
        $str = Registry::get('CaseHandler')->transformCaseFromTo(
            'snake_case',
            'camelCase',
            'controlled_vocab_entry_settings'
        );

        $this->assertSame('controlledVocabEntrySettings', $str);
    }

    public function testIdentifySnakeCase()
    {
        $case = $this->getStub()->callMethod('identifyCase','user_settings');
        $this->assertSame('snake_case', $case);
    }

    public function testIdentifyUppercase()
    {
        $case = $this->getStub()->callMethod('identifyCase', 'CANADA');
        $this->assertSame('UPPERCASE', $case);
    }

    public function testIdentifyPascalCase()
    {
        $case = $this->getStub()->callMethod('identifyCase', 'AllBlacks');
        $this->assertSame('PascalCase', $case);
    }

    public function testIdentifyCamelCase()
    {
        $case = $this->getStub()->callMethod(
            'identifyCase',
            'teNeiTeTangaTa'
        );

        $this->assertSame('camelCase', $case);
    }

    public function testIdentifyLowercase()
    {
        $case = $this->getStub()->callMethod(
            'identifyCase',
            'the rime of the ancient mariner'
        );

        $this->assertSame('lowercase', $case);
    }

    public function testTranformToSnakeCaseIdentifyingThatComesFromCamelCase()
    {
        $original = 'andTheShipSalesOn';
        $str = Registry::get('CaseHandler')->transformCaseTo(
            'snake_case',
            $original
        );

        $this->assertSame(
            'and_the_ship_sales_on',
            $str
        );
    }

    public function testTransformToPascalCaseIdentifyingThatComesFromSnakeCase()
    {
        $original = 'sailing_on_and_on_and_on_across_the_sea';
        $str = Registry::get('CaseHandler')->transformCaseTo(
            'PascalCase',
            $original
        );

        $this->assertSame(
            'SailingOnAndOnAndOnAcrossTheSea',
            $str
        );
    }
}
