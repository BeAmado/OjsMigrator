<?php

namespace BeAmado\OjsMigrator\Test;

abstract class XmlDataMappingExtensionTest extends FunctionalTest 
                                           implements StubInterface
{
    public abstract function getStub();

    protected static function fsm()
    {
        return \BeAmado\OjsMigrator\Registry::get('FileSystemManager');
    }

    protected static function mappingSandbox()
    {
        return self::fsm()->formPathFromBaseDir([
            'tests',
            '_data',
            'mapping_sandbox',
        ]);
    }

    protected static function createMappingSandboxIfNotExists()
    {
        if (!self::fsm()->dirExists(self::mappingSandbox()))
            self::fsm()->createDir(self::mappingSandbox());
    }

    protected static function copyXmlMappingsToTheMappingSandbox()
    {
        self::fsm()->copyFile(
            self::fsm()->formPath([
                self::fsm()->parentDir(self::mappingSandbox()),
                'mappings.xml',
            ]),
            self::mappingsFile()
        );
    }

    protected static function setUpMappingSandbox()
    {
        self::createMappingSandboxIfNotExists();
        self::copyXmlMappingsToTheMappingSandbox();
    }

    protected static function removeMappingSandbox()
    {
        self::fsm()->removeWholeDir(self::mappingSandbox());
    }

    public static function setUpBeforeClass($args = []) : void
    {
        parent::setUpBeforeClass($args);
        self::setUpMappingSandbox();
    }

    public static function tearDownAfterClass($args = []) : void
    {
        self::removeMappingSandbox();
        parent::tearDownAfterClass();
    }

    protected static function mappingsFile()
    {
        return self::fsm()->formPath([
            self::mappingSandbox(),
            'mappings.xml',
        ]);
    }
}
