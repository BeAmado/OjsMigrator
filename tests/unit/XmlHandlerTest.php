<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Util\XmlHandler;

class XmlHandlerTest extends TestCase
{
    private function getStub()
    {
        require_once(dirname(__FILE__) . '/../TestStub.php');
        require_once(dirname(__FILE__) . '/../WorkWithFiles.php');
        return new class extends XmlHandler {
            use BeAmado\OjsMigrator\TestStub;
            use BeAmado\OjsMigrator\WorkWithFiles;
        };
    }

    private function getBandsXmlFilename()
    {
        return $this->getStub()->getDataDir() . '/bands.xml';
    }

    public function testReadsXml()
    {
        $this->assertInstanceOf(
            DOMDocument::class,
            $this->getStub()->callMethod(
                'readXml',
                $this->getBandsXmlFilename()
            )
        );
    }
}
