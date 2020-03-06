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
    /*
    public static function setUpBeforeClass($args = []) : void
    {
        parent::setUpBeforeClass($args);
        (new FixtureHandler())->createSeveral([
            'journals' => ['test_journal'],
        ]);
    }
    */

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

    public function testCanGetPahByFileAbbrev()
    {
        $this->assertSame(
            'submission' . $this->sep() . 'copyedit',
            $this->handler()->getPathByFileAbbrev('CE')
        );
    }

    public function testCanFormTheCorrectTableName()
    {
        $this->assertSame(
            $this->table('files'),
            $this->getStub()->callMethod('formTableName')
        );
    }

    public function testCanGetTheCorrectDAO()
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

        $good = [
        ];
    }
}
