<?php

use BeAmado\OjsMigrator\Test\FunctionalTest;
use BeAmado\OjsMigrator\DataMappingManager;
use BeAmado\OjsMigrator\Registry;
use BeAmado\OjsMigrator\Test\StubInterface;
use BeAmado\OjsMigrator\Test\TestStub;
use BeAmado\OjsMigrator\Test\JournalMock;

class DataMappingManagerTest extends FunctionalTest implements StubInterface
{
    public static function setUpBeforeClass($args = [
        'setDataMappingManager' => false,
    ]) : void {
        parent::setUpBeforeClass($args);
    }

    public function tearDown() : void
    {
        if ($this->fsm()->dirExists($this->dataMappingDir()))
            $this->fsm()->removeDir($this->dataMappingDir());
    }

    public function getStub()
    {
        return new class extends DataMappingManager {
            use TestStub;
        };
    }

    protected function fsm()
    {
        return Registry::get('FileSystemManager');
    }

    protected function journal()
    {
        return (new JournalMock())->getTestJournal();
    }

    protected function dataMappingDir()
    {
        return $this->fsm()->formPathFromBaseDir([
            '.data_mapping',
            implode('-', [
                $this->journal()->getId(),
                $this->journal()->getData('path'),
            ])
        ]);
    }

    public function testTheDataMappingBaseDirectoryIsHiddenInTheAppBaseDir()
    {
        $this->assertSame(
            $this->getStub()->callMethod('getDataMappingBaseDir'),
            \BeAmado\OjsMigrator\BASE_DIR 
                . \BeAmado\OjsMigrator\DIR_SEPARATOR
                . '.data_mapping'
        );
    }

    public function testCanSetTheDataMappingDirectory()
    {
        $existedBefore = $this->fsm()->dirExists($this->dataMappingDir());
        Registry::get('DataMappingManager')->setDataMappingDir(
            $this->journal()
        );


        $this->assertSame(
            implode(';', [
                0,
                $this->dataMappingDir(),
                1,
            ]),
            implode(';', [
                (int) $existedBefore,
                Registry::get('DataMappingDir'),
                (int) $this->fsm()->dirExists($this->dataMappingDir()),
            ])
        );
    }

    /**
     * @depends testCanSetTheDataMappingDirectory
     */
    public function testCanGetTheDataMappingDirectory()
    {
        Registry::get('DataMappingManager')->setDataMappingDir(
            $this->journal()
        );

        $this->assertSame(
            Registry::get('FileSystemManager')->formPathFromBaseDir([
                '.data_mapping',
                \implode('-', [
                    $this->journal()->getId(),
                    $this->journal()->getData('path'),
                ])
            ]),
            Registry::get('DataMappingManager')->getDataMappingDir()
        );
    }
}
