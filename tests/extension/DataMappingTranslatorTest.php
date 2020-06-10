<?php

use BeAmado\OjsMigrator\Extension\DataMappingTranslator;
use BeAmado\OjsMigrator\Test\XmlDataMappingExtensionTest as ExtensionTest;
use BeAmado\OjsMigrator\Test\TestStub;
use BeAmado\OjsMigrator\Registry;
use BeAmado\OjsMigrator\DataMappingManager;

class DataMappingTranslatorTest extends ExtensionTest
{
    public static function setUpBeforeClass($args = [
        'createTables' => ['journals'],
    ]) : void {
        parent::setUpBeforeClass($args);
    }

    public function getStub()
    {
        return new class(
            self::mappingsFile(),
            self::mappingSandbox()
        ) extends DataMappingTranslator {
            use TestStub;
        };
    }

    protected function translator()
    {
        return new DataMappingTranslator(
            self::mappingsFile(),
            self::mappingSandbox()
        );
    }

    public function testCanGetTheMappingsForAnEntity()
    {
        $this->assertSame(
            '1:2021,2:2022,3:2023,4:2024,5:2025',
            implode(',', array_map(function($mapping) {
                return implode(':', [
                    $mapping['old'],
                    $mapping['new'],
                ]);
            }, $this->getStub()->callMethod('getMapping', 'users')))
        );
    }

    public function testMapTheDataForAnEntity()
    {
        $this->getStub()->callMethod(
            'mapData',
            'review_forms'
        );

        $this->assertSame(
            '11-12-13-14-15-16-17',
            implode('-', array_map(function($id) {
                return Registry::get('DataMapper')->getMapping(
                    'review_forms',
                    $id
                );
            }, [1, 2, 3, 4, 5, 6, 7]))
        );
    }

    public function testCanGetTheJournalPathMapping()
    {
        $mappings = $this->getStub()->callMethod(
            'getMapping',
            [
                'entity' => 'paths',
                'field' => 'journal_path',
            ]
        );
        $this->assertSame(
            '1-ancient_wind-new_wave',
            implode('-', [
                count($mappings),
                $mappings[0]['old'],
                $mappings[0]['new'],
            ])
        );
    }

    public function testCanGetTheJournalData()
    {
        $this->assertSame(
            json_encode([
                'old' => ['id' => '1', 'path' => 'ancient_wind'],
                'new' => ['id' => '6', 'path' => 'new_wave'],
            ]),
            json_encode($this->getStub()->callMethod('getJournalData'))
        );
    }

    /**
     * @depends testCanGetTheJournalData
     */
    public function testCanGetTheJournalToMap()
    {
        $journalData = $this->getStub()->callMethod('getJournalData');
        Registry::get('JournalsDAO')->create([
            'journal_id' => $journalData['new']['id'],
            'path' => $journalData['new']['path'],
        ]);

        Registry::get('JournalsDAO')->update([
            'set' => [
                'journal_id' => $journalData['new']['id'],
            ],
            'where' => [
                'path' => $journalData['new']['path'],
            ],
        ]);

        $journal = $this->translator()->getJournal();

        $this->assertSame(
            '1-1-1',
            implode('-', [
                (int) Registry::get('EntityHandler')->isEntity($journal),
                (int) $this->areEqual(
                    $journal->getId(),
                    $journalData['new']['id']
                ),
                (int) $this->areEqual(
                    $journal->getData('path'),
                    $journalData['new']['path']
                ),
            ])
        );
    }

    public function testCanCheckWhichEntitiesAreMapped()
    {
        $mappedEntities = $this->getStub()->callMethod(
            'separateMappingsForEachEntity'
        );
        $this->assertTrue(Registry::get('ArrayHandler')->equals(
            $mappedEntities,
            ['articles', 'sections', 'review_forms', 'journals', 'users',]
        ));
    }

    /**
     * @depends testCanGetTheJournalToMap
     */
    public function testCanTranslateAllTheMappingsInTheXmlMappingsFile()
    {
        self::removeMappingSandbox();
        self::setUpMappingSandbox();
        $mapped = $this->translator()->translateAllMappings();
        $this->assertSame(
            '1-1-1-1-1-1-1',
            implode('-', [
                (int) array_reduce([
                    'articles',
                    'journals',
                    'review_forms',
                    'sections',
                    'users',
                ], function($c, $entity) {
                    return $c && self::fsm()->dirExists(self::fsm()->formPath([
                        self::fsm()->parentDir(__DIR__), // [...]/OjsMigrator/tests
                        '_data',
                        'sandbox',
                        'data_mapping',
                        '6-new_wave',
                        $entity,
                    ]));
                }, true),
                (int) Registry::get('ArrayHandler')->equals(
                    $mapped,
                    [
                        'articles' => true,
                        'journals' => true,
                        'review_forms' => true,
                        'sections' => true,
                        'users' => true,
                    ]
                ),
                (int) $this->areEqual(
                    2022,
                    Registry::get('DataMapper')->getMapping('users', 2)
                ),
                (int) $this->areEqual(
                    13,
                    Registry::get('DataMapper')->getMapping('review_forms', 3)
                ),
                (int) $this->areEqual(
                    104,
                    Registry::get('DataMapper')->getMapping('sections', 4)
                ),
                (int) $this->areEqual(
                    6,
                    Registry::get('DataMapper')->getMapping('journals', 1)
                ),
                (int) $this->areEqual(
                    1002,
                    Registry::get('DataMapper')->getMapping('articles', 2)
                ),
            ])
        );
    }
}
