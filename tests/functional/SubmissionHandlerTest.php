<?php

use BeAmado\OjsMigrator\FunctionalTest;
use BeAmado\OjsMigrator\Registry;
use BeAmado\OjsMigrator\Entity\SubmissionHandler;

// interfaces 
use BeAmado\OjsMigrator\TestStub;

// traits
use BeAmado\OjsMigrator\StubInterface;

class SubmissionHandlerTest extends FunctionalTest
{
    public function getStub()
    {
        return new class extends SubmissionHandler {
            use TestStub;
        };
    }

    public function testTheSubmissionAliasIsArticle()
    {
        $this->assertSame(
            'article',
            $this->getStub()->getEntityAlias()
        );
    }

    public function testCanFormTheTableNameForTheArticleFiles()
    {
        $this->assertSame(
            'article_files',
            Registry::get('SubmissionHandler')->formTableName('files')
        );
    }
}
