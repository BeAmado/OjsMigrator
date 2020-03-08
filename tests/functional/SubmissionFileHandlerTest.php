<?php

use BeAmado\OjsMigrator\Entity\SubmissionFileHandler;
use BeAmado\OjsMigrator\Registry;
use BeAmado\OjsMigrator\Test\FunctionalTest;
use BeAmado\OjsMigrator\Test\FixtureHandler;

// interfaces
use BeAmado\OjsMigrator\Test\StubInterface;

// traits
use BeAmado\OjsMigrator\Test\TestStub;

class SubmissionFileHandlerTest extends FunctionalTest implements StubInterface
{
    public static function setUpBeforeClass($args = [
        'createTables' => [
            'submissions',
            'submission_files',
        ],
    ]) : void {
        parent::setUpBeforeClass($args);
    }

    protected function sep()
    {
        return \BeAmado\OjsMigrator\DIR_SEPARATOR;
    }

    protected function handler()
    {
        return Registry::get('SubmissionFileHandler');
    }

    protected function table($name)
    {
        return Registry::get('SubmissionHandler')->formTableName($name);
    }

    public function getStub()
    {
        return new class extends SubmissionFileHandler {
            use TestStub;
        };
    }

    public function testCanFormAFilePath()
    {
        $this->assertSame(
            'path' . $this->sep() . 'to' . $this->sep() . 'file',
            $this->getStub()->callMethod(
                'formPath',
                array(
                    'parts' => array(
                        'path', 'to', 'file',
                    )
                )
            )
        );
    }

    public function testCanGetTheValidAbbrevs()
    {
        $abbrevs = array(
            'SM',
            'RV',
            'ED',
            'CE',
            'LE',
            'SP',
            'PB',
            'NT',
            'AT',
        );
        $this->assertTrue(Registry::get('ArrayHandler')->equals(
            $abbrevs,
            $this->getStub()->callMethod('getValidAbbrevs')
        ));
    }

    public function testCanGetAFileSchemaByName()
    {
        $this->assertTrue(Registry::get('ArrayHandler')->equals(
            [
                'stage' => 7,
                'abbrev' => 'PB',
                'path' => 'public',
            ],
            $this->getStub()->callMethod(
                'searchFileSchemaByName',
                'publish'
            )
        ));
    }

    public function testCanGetAFileSchemaByStage()
    {
        $this->assertTrue(Registry::get('ArrayHandler')->equals(
            [
                'stage' => 9,
                'abbrev' => 'AT',
                'path' => 'attachment',
            ],
            $this->getStub()->callMethod(
                'searchFileSchemaByStage',
                9
            )
        ));
    }

    public function testCanGetAFileSchemaByAbbrev()
    {
        $this->assertTrue(Registry::get('ArrayHandler')->equals(
            [
                'stage' => 2,
                'abbrev' => 'RV',
                'path' => 'submission' 
                    . \BeAmado\OjsMigrator\DIR_SEPARATOR . 'review',
            ],
            $this->getStub()->callMethod(
                'searchFileSchemaByAbbrev',
                'RV'
            )
        ));
    }

    public function testCanGetPathByFileStage()
    {
        $this->assertSame(
            'note',
            $this->handler()->getPathByFileStage(8)
        );
    }

    public function testCanGetPathByFileAbbrev()
    {
        $fsm = Registry::get('FileSystemManager');
        $schema = [
            [
                'abbrev' => 'SM',
                'path' => $fsm->formPath(['submission', 'original']),
            ],
            [
                'abbrev' => 'RV',
                'path' => $fsm->formPath(['submission', 'review']),
            ],
            [
                'abbrev' => 'ED',
                'path' => $fsm->formPath(['submission', 'editor']),
            ],
            [
                'abbrev' => 'CE',
                'path' => $fsm->formPath(['submission', 'copyedit']),
            ],
            [
                'abbrev' => 'LE',
                'path' => $fsm->formPath(['submission', 'layout']),
            ],
            [
                'abbrev' => 'SP',
                'path' => 'supp',
            ],
            [
                'abbrev' => 'PB',
                'path' => 'public',
            ],
            [
                'abbrev' => 'NT',
                'path' => 'note',
            ],
            [
                'abbrev' => 'AT',
                'path' => 'attachment',
            ],
        ];
        $this->assertSame(
            implode('-', array_map(function($s){ 
                return $s['path']; 
            }, $schema)),
            array_reduce($schema, function($carry, $s) {
                if ($carry === null)
                    return $this->handler()->getPathByFileAbbrev($s['abbrev']);

                return $carry 
                    . '-' 
                    . $this->handler()->getPathByFileAbbrev($s['abbrev']);
            })
        );
    }

    public function testCanFormTheCorrectTableName()
    {
        $this->assertSame(
            $this->table('files'),
            $this->getStub()->callMethod('formTableName')
        );
    }

    public function testCanGetTheCorrectDao()
    {
        $dao = $this->getStub()->callMethod('getDAO');
        $this->assertSame(
            implode('-', [
                1,
                $this->table('files'),
            ]),
            implode('-', [
                (int) is_a($dao, BeAmado\OjsMigrator\Db\DAO::class),
                $dao->getTableName(),
            ])
        );
    }

    public function testCanGetAMappedFileName()
    {
        $filename = '199-876-2-LE.doc';
        $newFilename = '14-23-2-LE.doc';

        Registry::get('DataMapper')->mapData(
            $this->table('submissions'),
            ['old' => 199, 'new' => 14]
        );
        
        Registry::get('DataMapper')->mapData(
            $this->table('files'),
            ['old' => 876, 'new' => 23]
        );

        $this->assertSame(
            $newFilename,
            $this->getStub()->callMethod(
                'getMappedFileName',
                $filename
            )
        );
    }

    public function testAFileWhichStageIsNotBetween1And9IsNotOk()
    {
        $bad = [
            $this->handler()->create(['file_stage' => -1]),
            $this->handler()->create(['file_stage' => 'a']),
            $this->handler()->create([]),
            $this->handler()->create(['file_stage' => 23]),
        ];

        $good = [];

        for($i = 1; $i <=9; $i++) {
            $good[] = $this->handler()->create(['file_stage' => $i]);
        }

        $allTrue = function($carry, $file) {
            return $carry && $this->handler()->fileStageOk($file);
        };

        $allFalse = function($carry, $file) {
            return $carry || $this->handler()->fileStageOk($file);
        };

        $this->assertSame(
            '0-1',
            implode('-', [
                (int) array_reduce($bad, $allFalse, false),
                (int) array_reduce($good, $allTrue, true),
            ])
        );
    }

    public function testCanGetTheAbbrevFromTheFileName()
    {
        $filenames = [
            '28912-1123-2-RV.pdf',
            '21-212-1-PB.pdf',
            '210-210212-9-CE.pdf'
        ];

        $this->assertSame(
            'RV;PB;CE',
            array_reduce($filenames, function($carry, $item){
                $abbrev = $this->getStub()->callMethod(
                    'getAbbrevFromFileName',
                    $item
                );
                return empty($carry) ? $abbrev : ($carry . ';' . $abbrev);
            })
        );
    }

    public function testCanValidateIfTheSubmissionFileNameIsOk()
    {
        $bad = [
            $this->handler()->create(['file_name' => 'Nobody']),
            $this->handler()->create(['file_name' => '8293-3902-PB.pdf']),
            $this->handler()->create(['file_name' => '82934-234-8923.RV.doc']),
        ];

        foreach ([
            'Nobody',
            '8391-1212-PB.pdf',
            '9012-29102-129012.RV.doc',
        ] as $filename) {
            $bad[] = $this->handler()->create(['file_name' => $filename]);
        }

        $good = [];

        foreach ([
            '982-323-392-RV.doc',
            '92-12-1-LE.xml',
            '921-2-2-PB.pdf',
            '291-21-2123121-SM.docx',
        ] as $filename) {
            $good[] = $this->handler()->create(['file_name' => $filename]);
        }
        
        $this->assertSame(
            '1-0',
            implode('-', [
                (int) array_reduce($good, function($carry, $file){
                    return $carry && $this->handler()->fileNameOk($file);
                }, true),
                (int) array_reduce($bad, function($carry, $file){
                    return $carry || $this->handler()->fileNameOk($file);
                }, false),
            ])
        );
    }

    public function testCanFormTheSubmissionsDirectoryForTheJournalWithId19()
    {
        $expected = Registry::get('FileSystemManager')->formPath([
            Registry::get('ConfigHandler')->getFilesDir(),
            'journals',
            '19',
            Registry::get('SubmissionHandler')->formTableName(),
        ]);
        $this->assertSame(
            "$expected-$expected",
            implode('-', array_map(
                function($journal) {
                    return $this->getStub()->callMethod(
                        'getJournalSubmissionsDir',
                        $journal
                    );
                }, 
                [
                    19, 
                    Registry::get('JournalHandler')->create([
                        'journal_id' => 19
                    ]),
               ]
            ))
        );
    }

    public function testCanFormTheFileNameFullpathUsingTheFileStage()
    {
        $file = $this->handler()->create([
            Registry::get('SubmissionHandler')->formIdField() => 67,
            'file_name' => 'anyname',
            'file_stage' => 2,
        ]);

        $expected = Registry::get('FileSystemManager')->formPath([
            Registry::get('ConfigHandler')->getFilesDir(),
            'journals',
            '19',
            Registry::get('SubmissionHandler')->formTableName(),
            '67',
            'submission',
            'review',
            'anyname',
        ]);

        $this->assertSame(
            $expected,
            $this->getStub()->callMethod(
                'formPathByFileStage',
                [
                    'file' => $file,
                    'journal' => 19,
                ]
            )
        );
    }
    
    public function testCanFormTheFileNameFullpathUsingTheFileName()
    {
        $file = $this->handler()->create([
            Registry::get('SubmissionHandler')->formIdField() => 67,
            'file_name' => '67-88-2-SP.doc',
        ]);

        $expected = Registry::get('FileSystemManager')->formPath([
            Registry::get('ConfigHandler')->getFilesDir(),
            'journals',
            '19',
            Registry::get('SubmissionHandler')->formTableName(),
            '67',
            'supp',
            '67-88-2-SP.doc',
        ]);

        $this->assertSame(
            $expected,
            $this->getStub()->callMethod(
                'formPathByFileName',
                [
                    'file' => $file,
                    'journal' => 19,
                ]
            )
        );
    }

    public function testCanUpdateTheFileNameOfTheCorrespondingFileInDatabase()
    {
        $smHr = Registry::get('SubmissionHandler');
        $submission = $smHr->create([
            $smHr->formIdField() => 12,
        ]);

        $smHr->createOrUpdateInDatabase($submission);
        $nameBefore = '12-23-1-NT.doc';

        $file = Registry::get('SubmissionFileHandler')->create([
            $smHr->formIdField() => Registry::get('DataMapper')->getMapping(
                $smHr->formTableName(),
                12
            ),
            'file_id' => 23,
            'file_stage' => 8,
            'file_name' => $nameBefore,
            'revision' => 1,
        ]);

        $smHr->createOrUpdateInDatabase($file);

        $mappedName = $this->getStub()->callMethod(
            'getMappedFileName',
            $nameBefore
        );

        $this->getStub()->callMethod(
            'updateFileNameInDatabase',
            $mappedName
        );

        $fromDb = $this->getStub()->callMethod('getDAO')->read([
            'file_id' => Registry::get('DataMapper')->getMapping(
                $this->table('files'),
                23
            )
        ]);

        $this->assertSame(
            '1',
            implode('-', [
                $fromDb->length(),
            ])
        );

    }

    public function testCanFormTheFilePathInTheEntitiesDir()
    {
        $filename = '9101-212-2-PB.pdf';
        $expected = Registry::get('FileSystemManager')->formPathFromBaseDir([
            'tests',
            '_data',
            'sandbox',
            'entities',
            Registry::get('SubmissionHandler')->formTableName(),
            explode('-', $filename)[0],
            $filename,
        ]);

        $this->assertSame(
            $expected,
            $this->getStub()->callMethod(
                'formFilePathInEntitiesDir',
                $filename
            )
        );
    }

    public function testCanCopyTheFileToTheJournalSubmissionsDir()
    {
        $filename = '21-34-1-LE.doc';
        $content = 'Quelque chose pour tester.';
        Registry::get('FileHandler')->write(
            $this->getStub()->callMethod(
                'formFilePathInEntitiesDir', 
                $filename
            ),
            $content
        );

        $this->getStub()->callMethod(
            'copyFileToJournalSubmissionsDir',
            [
                'filename' => $filename,
                'journal' => 32,
            ]
        );

        $expectedFilename = Registry::get('FileSystemManager')
                                    ->formPathFromBaseDir([
            'tests',
            '_data',
            'sandbox',
            'ojs2',
            'files',
            'journals',
            '32',
            $this->table('submissions'),
            '21',
            'submission',
            'layout',
            $filename,
        ]);

        $this->assertSame(
            "1-$content",
            implode('-', [
                file_exists($expectedFilename),
                Registry::get('FileHandler')->read($expectedFilename),
            ])
        );
    }

    public function testCanImportASubmissionFile()
    {
        $journal = Registry::get('JournalHandler')->create([
            'journal_id' => 77,
            'path' => 'lemonde',
        ]);

        $smHr = Registry::get('SubmissionHandler');

        $submission = $smHr->create([
            $smHr->formIdField() => 33,
        ]);
        
        $filename = '33-44-2-CE.pdf';

        $file = $this->handler()->create([
            'file_id' => 44,
            $smHr->formIdField() => 33,
            'file_name' => $filename,
            'stage' => 4,
            'source_file_id' => 23,
            'revision' => 2,
        ]);

        $content = 'Let it go';

        Registry::get('FileHandler')->write(
            Registry::get('FileSystemManager')->formPathFromBaseDir([
                'tests',
                '_data',
                'sandbox',
                'entities',
                $smHr->formTableName(),
                '33',
                $filename,
            ]),
            $content
        );

        $smHr->createOrUpdateInDatabase($submission);

        $imported = Registry::get('SubmissionFileHandler')
                            ->importSubmissionFile(
            $file,
            $journal
        );

        $mappedFilename = $this->getStub()->callMethod(
            'getMappedFileName',
            $filename
        );

        $fromDb = $smHr->getDAO('files')->read([
            'revision' => 2,
            'file_id' => Registry::get('DataMapper')->getMapping(
                $this->table('files'),
                $file->getId()
            ),
        ]);

        $submissionId = Registry::get('DataMapper')->getMapping(
            $smHr->formTableName(),
            $submission->getId()
        );

        $fullpath = $this->getStub()->callMethod(
            'formPathByFileName',
            [
                'filename' => $mappedFilename,
                'journal' => $journal,
            ]
        );

        //Registry::get('TimeKeeper')->wait(5000);

        $this->assertSame(
            "1;1;1;$mappedFilename;1;$content",
            implode(';', [
                (int) $imported,
                (int) file_exists($this->getStub()->callMethod(
                    'formFilePathInEntitiesDir',
                    $filename
                )),
                $fromDb->length(),
                $fromDb->get(0)->getData('file_name'),
                (int) file_exists($fullpath),
                Registry::get('FileHandler')->read($fullpath),
            ])
        );
    }
}
