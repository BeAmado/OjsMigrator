<?php

use BeAmado\OjsMigrator\Test\FunctionalTest;
use BeAmado\OjsMigrator\Entity\SubmissionHistoryHandler;
use BeAmado\OjsMigrator\Registry;
use BeAmado\OjsMigrator\Test\FixtureHandler;

// interfaces
use BeAmado\OjsMigrator\Test\StubInterface;

// traits
use BeAmado\OjsMigrator\Test\TestStub;

// mocks
use BeAmado\OjsMigrator\Test\SubmissionMock;

class SubmissionHistoryHandlerTest extends FunctionalTest
{
    public static function setUpBeforeClass($args = [
        'createTables' => [
            'submissions',
            'event_log',
            'event_log_settings',
            'email_log',
            'email_log_users',
        ],
    ]) : void {
        parent::setUpBeforeClass($args);
        (new FixtureHandler())->createSeveral([
            'users' => [
                'hulk',
                'thor',
            ],
        ]);

        Registry::get('EntityHandler')->createOrUpdateInDatabase(
            (new SubmissionMock())->getSubmission('rwc2015')
        );
    }

    public function getStub()
    {
        return new class extends SubmissionHistoryHandler {
            use TestStub;
        };
    }

    protected function createRWC2015()
    {
        return (new SubmissionMock())->getSubmission('rwc2015');
    }

    protected function handler()
    {
        return Registry::get('SubmissionHandler');
    }

    public function testCanImportAnEventLog()
    {
        $user = Registry::get('UserHandler')->create([
            'user_id' => 33,
            'username' => 'mario',
            'email' => 'itsme@mario.com',
            'first_name' => 'Mario',
            'last_name' => 'Broghesi',
        ]);
        $createdUser = $this->handler()->createOrUpdateInDatabase($user);

        $submission = $this->handler()->create([
            $this->handler()->formIdField() => 13,
            'user_id' => 33,
            'journal_id' => 21,
            'section_id' => 999,
        ]);
        $createdSubmission = $this->handler()
                                  ->createOrUpdateInDatabase($submission);

        $eventLog = Registry::get('EntityHandler')->create('event_log', [
            'log_id' => 442,
            'assoc_type' => 257,
            'assoc_id' => 13,
            'message' => 'mamma mia',
        ]);
        $imported = $this->getStub()->callMethod(
            'importEventLog',
            $eventLog
        );

        $this->assertSame(
            '1-1-1',
            implode('-', [
                (int) $createdSubmission,
                (int) $createdUser,
                (int) $imported,
            ])
        );
    }

    public function testCanImportTheSubmissionHistory()
    {
        $submission = $this->createRWC2015();
        $imported = Registry::get('SubmissionHistoryHandler')
                            ->importHistory($submission);

        $submissionId = Registry::get('DataMapper')->getMapping(
            $this->handler()->formTableName(),
            $submission->getId()
        );

        $eventLogs = Registry::get('EventLogDAO')->read([
            'assoc_type' => 257,
            'assoc_id' => $submissionId,
        ]);
        $eventLogSettings = Registry::get('EventLogSettingsDAO')->read([
            'log_id' => $eventLogs->get(0)->getId(),
        ]);

        $emailLogs = Registry::get('EmailLogDAO')->read([
            'assoc_type' => 257,
            'assoc_id' => $submissionId,
        ]);
        $emailLogUsers = Registry::get('EmailLogUsersDAO')->read([
            'email_log_id' => $emailLogs->get(0)->getId(),
        ]);

        $this->assertSame(
            '1-1-1-2-1',
            implode('-', [
                (int) $imported,
                $eventLogs->length(),
                $emailLogs->length(),
                $eventLogSettings->length(),
                $emailLogUsers->length(),
            ])
        );
    }
}
