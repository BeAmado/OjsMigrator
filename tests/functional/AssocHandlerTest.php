<?php

use BeAmado\OjsMigrator\Test\FunctionalTest;
use BeAmado\OjsMigrator\Entity\AssocHandler;
use BeAmado\OjsMigrator\Registry;

use BeAmado\OjsMigrator\Test\StubInterface;
use BeAmado\OjsMigrator\Test\TestStub;

class AssocHandlerTest extends FunctionalTest implements StubInterface
{
    public function getStub()
    {
        return new class extends AssocHandler {
            use TestStub;
        };
    }

    public function testCanFormTheRightTableForUserRoles()
    {
        $this->assertSame(
            'roles',
            $this->getStub()->callMethod(
                'formTableName',
                'user_roles'
            )
        );
    }

    public function testCanFormTheCorrectTableNames()
    {
        $this->assertSame(
            implode(';', [
                'users',
                'groups',
                'roles',
                'issues',
                Registry::get('SubmissionHandler')->formTableName(),
                Registry::get('SubmissionHandler')->formTableName(
                    'supplementary_files'
                ),
            ]),
            implode(';', array_map(function($name) {
                return $this->getStub()->callMethod(
                    'formTableName',
                    $name
                );
            }, [
                'user',
                'user_group',
                'user_roles',
                'issues',
                'submission',
                'supp_file',
            ]))
        );
    }

    public function testJournalIsAssocType256()
    {
        $this->assertSame(
            'journals',
            Registry::get('AssocHandler')->getAssocTableName(256)
        );
    }

    public function testSubmissionIsAssocType257()
    {
        $this->assertSame(
            Registry::get('SubmissionHandler')->formTableName(),
            Registry::get('AssocHandler')->getAssocTableName(257)
        );
    }
}
