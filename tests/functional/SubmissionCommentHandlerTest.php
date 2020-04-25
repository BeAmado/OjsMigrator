<?php

use BeAmado\OjsMigrator\Entity\SubmissionCommentHandler;
use BeAmado\OjsMigrator\Entity\SubmissionHandler;
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
            'review_rounds',
        ],
    ]) : void {
        parent::setUpBeforeClass($args);

        (new FixtureHandler())->createSeveral([
            'journals' => ['test_journal'],
            'users' => [
                'ironman',
                'hulk',
            ],
            'sections' => [
                'sciences',
                'sports',
            ],
        ]);
    }

    public function getStub()
    {
        return new class extends SubmissionCommentHandler {
            use TestStub;
        };
    }

    protected function getSmHrStub()
    {
        return new class extends SubmissionHandler {
            use TestStub;
        };
    }

    protected function handler()
    {
        return Registry::get('SubmissionHandler');
    }

    protected function commentsDAO()
    {
        return $this->handler()->getDAO('comments');
    }

    protected function createRWC2015()
    {
        return (new SubmissionMock())->getRWC2015();
    }

    protected function createTRC2015()
    {
        return (new SubmissionMock())->getTRC2015();
    }

    public function testCanImportASubmissionComment()
    {
        $submission = $this->CreateRWC2015();

        $registered = $this->getSmHrStub()->callMethod(
            'registerSubmission',
            $submission
        );

        $comment = $submission->get('comments')->get(0);

        $commentsBefore = $this->commentsDAO()->read();

        $imported = $this->getStub()->callMethod(
            'importSubmissionComment',
            $comment
        );

        $commentId = Registry::get('DataMapper')->getMapping(
            $this->handler()->formTableName('comments'),
            $comment->get('comment_id')->getValue()
        );

        $commentFromDb = $this->commentsDAO()->read([
            'comment_id' => $commentId,
        ]);

        $this->assertSame(
            '1-0-1-1-1-1-1-1',
            implode('-', [
                (int) $registered,
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

    public function testCanImportTheCommentsOfASubmission()
    {
        $submission = $this->createTRC2015();

        $registered = $this->getSmHrStub()->callMethod(
            'registerSubmission',
            $submission
        );

        $importedReviewRound = $this->getSmHrStub()->callMethod(
            'importReviewRound',
            $submission->get('review_rounds')->get(0)
        );
        $importedReviewAssignment = $this->getSmHrStub()->callMethod(
            'importReviewAssignment',
            $submission->get('review_assignments')->get(0)
        );

        $imported = Registry::get('SubmissionCommentHandler')->importComments(
            $submission
        );

        $submissionId = Registry::get('DataMapper')->getMapping(
            $this->handler()->formTableName(),
            $submission->getId()
        );

        $commentsFromDb = $this->commentsDAO()->read([
            $this->handler()->formIdField() => $submissionId,
        ]);

        $submission->get('comments')->forEachValue(function($comment) {
            $this->handler()->setMappedData($comment, [
                'users' => 'author_id',
                $this->handler()->formTableName('comments') => 'comment_id',
                $this->handler()->formTableName() => $this->handler()
                                                          ->formIdField(),
            ]);

            $this->handler()->setMappedData($comment, [
                $this->getStub()->callMethod(
                    'getAssocTable',
                    $comment
                ) => 'assoc_id',
            ]);
        });

        $this->assertSame(
            '1-1-1-1-2-1',
            implode('-', [
                (int) $registered,
                (int) $importedReviewRound,
                (int) $importedReviewAssignment,
                (int) $imported,
                $commentsFromDb->length(),
                (int) Registry::get('ArrayHandler')->areEquivalent(
                    $commentsFromDb->toArray(),
                    $submission->get('comments')->toArray()
                ),
            ])
        );
    }

    /**
     * @depends testCanImportASubmissionComment
     * @depends testCanImportTheCommentsOfASubmission
     */
    public function testCanGetTheCommentsOfASubmission()
    {
        $trc2015 = $this->createTRC2015();
        $rwc2015 = $this->createRWC2015();

        \array_reduce([$trc2015, $rwc2015], function($carry, $sm) {
            $sm->get('comments')->forEachValue(function($comment) {
                $this->handler()->setMappedData($comment, [
                    'users' => 'author_id',
                    $this->handler()->formTableName('comments') => 'comment_id',
                    $this->handler()->formTableName() => $this->handler()
                                                              ->formIdField(),
                ]);

                $this->handler()->setMappedData($comment, [
                    $this->getStub()->callMethod(
                        'getAssocTable',
                        $comment
                    ) => 'assoc_id',
                ]);
            });
        });

        $commentsTRC2015 = Registry::get(
            'SubmissionCommentHandler'
        )->getSubmissionComments(Registry::get('DataMapper')->getMapping(
            $this->handler()->formTableName(),
            $trc2015->getId()
        ));

        $commentsRWC2015 = Registry::get(
            'SubmissionCommentHandler'
        )->getSubmissionComments(Registry::get('DataMapper')->getMapping(
            $this->handler()->formTableName(),
            $rwc2015->getId()
        ));

        $this->assertSame(
            '1-2-1-1',
            implode('-', [
                $commentsRWC2015->length(),
                $commentsTRC2015->length(),
                (int) Registry::get('EntityHandler')->areEqual(
                    $commentsRWC2015->get(0),
                    $rwc2015->get('comments')->get(0)
                ),
                (int) Registry::get('ArrayHandler')->areEquivalent(
                    $commentsTRC2015->toArray(),
                    $trc2015->get('comments')->toArray()
                ),
            ])
        );
    }
}
