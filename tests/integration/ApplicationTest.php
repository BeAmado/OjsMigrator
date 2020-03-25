<?php

use BeAmado\OjsMigrator\Test\IntegrationTest;
use BeAmado\OjsMigrator\Application;
use BeAmado\OjsMigrator\Registry;

///////// interfaces /////////////////
use BeAmado\OjsMigrator\Test\StubInterface;

/////////// traits ///////////////////
use BeAmado\OjsMigrator\Test\TestStub;

class ApplicationTest extends IntegrationTest implements StubInterface
{
    public function getStub()
    {
        return new class extends Application {
            use TestStub;
        };
    }

    public function testCanGetTheEntityHandlers()
    {
        $handlers = [];
        foreach ([
            'schrubbles',
            'announcements',
            'groups',
            'issues',
            'journals',
            'review_forms',
            'sections',
            'submissions',
            'users',
            'nothing',
        ] as $tableName) {
            $handlers[$tableName] = $this->getStub()->callMethod(
                'getHandler',
                $tableName
            );
        }

        $this->assertSame(
            '1-1-1-1-1-1-1-1-1-1',
            implode('-', [
                (int) is_null($handlers['schrubbles']),
                (int) is_null($handlers['nothing']),
                (int) is_a(
                    $handlers['announcements'],
                    BeAmado\OjsMigrator\Entity\AnnouncementHandler::class
                ),
                (int) is_a(
                    $handlers['groups'],
                    BeAmado\OjsMigrator\Entity\GroupHandler::class
                ),
                (int) is_a(
                    $handlers['issues'],
                    BeAmado\OjsMigrator\Entity\IssueHandler::class
                ),
                (int) is_a(
                    $handlers['journals'],
                    BeAmado\OjsMigrator\Entity\JournalHandler::class
                ),
                (int) is_a(
                    $handlers['review_forms'],
                    BeAmado\OjsMigrator\Entity\ReviewFormHandler::class
                ),
                (int) is_a(
                    $handlers['sections'],
                    BeAmado\OjsMigrator\Entity\SectionHandler::class
                ),
                (int) is_a(
                    $handlers['submissions'],
                    BeAmado\OjsMigrator\Entity\SubmissionHandler::class
                ),
                (int) is_a(
                    $handlers['users'],
                    BeAmado\OjsMigrator\Entity\UserHandler::class
                ),
            ])
        );
    }
}
