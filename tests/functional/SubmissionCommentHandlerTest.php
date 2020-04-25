<?php

use BeAmado\OjsMigrator\Entity\SubmissionCommentHandler;
use BeAmado\OjsMigrator\Registry;
use BeAmado\OjsMigrator\Test\FunctionalTest;
use BeAmado\OjsMigrator\Test\StubInterface;
use BeAmado\OjsMigrator\Test\TestStub;
use BeAmado\OjsMigrator\Test\SubmissionMock;
use BeAmado\OjsMigrator\Test\FixtureHandler;

class SubmissionCommentHandlerTest
extends FunctionalTest
implements StubInterface
{
    public static function setUpBeforeClass($args = [
        'createTables' => [
            'submissions',
            'submission_comments',
            'review_assignments',
        ],
    ]) : void {
        parent::setUpBeforeClass($args);

        (new FixtureHandler())->createSeveral([
            'journals' => ['test_journal'],
            'users' => [
                'ironman',
                'hulk',
            ],
        ]);
    }

    public function getStub()
    {
        return new class extends SubmissionCommentHandler {
            use TestStub;
        };
    }

    protected function handler()
    {
        return Registry::get('SubmissionHandler');
    }

    protected function createRWC2015()
    {
        return (new SubmissionMock())->getRWC2015();
    }

    public function testCanImportASubmissionComment()
    {
        $submission = $this->CreateRWC2015();
        $this->handler()->createOrUpdateInDatabase($submission);

        $comment = $submission->get('comments')->get(0);

        $commentsBefore = $this->handler()->getDAO('comments')->read();

        $imported = $this->getStub()->callMethod(
            'importSubmissionComment',
            $comment
        );

        $commentId = Registry::get('DataMapper')->getMapping(
            $this->handler()->formTableName('comments'),
            $comment->get('comment_id')->getValue()
        );

        $commentFromDb = $this->handler()->getDAO('comments')->read([
            'comment_id' => $commentId,
        ]);

        $this->assertSame(
            '0-1-1-1-1-1-1',
            implode('-', [
                (int) $commentsBefore,
                (int) $imported,
                $commentFromDb->length(),
                (int) $this->areEqual(
                    Registry::get('DataMapper')->getMapping(
                        $this->handler()->formTableName(),
                        $submission->getId()
                    ),
                    $commentFromDb->get(0)
                                  ->getData($this->handler()->formIdField())
                ),
                (int) $this->areEqual(
                    Registry::get('DataMapper')->getMapping(
                        'users',
                        $comment->get('author_id')->getValue()
                    ),
                    $commentFromDb->get(0)->getData('author_id')
                ),
                (int) $this->areEqual(
                    Registry::get('DataMapper')->getMapping(
                        $this->handler()->formTableName(),
                        $submission->getId()
                    ),
                    $commentFromDb->get(0)->getData('assoc_id')
                ),
                (int) $this->handler()->areEqual(
                    $commentFromDb->get(0),
                    $comment,
                    ['author_id', $this->handler()->formIdField(), 'assoc_id']
                ),
            ])
        );
    }
}
