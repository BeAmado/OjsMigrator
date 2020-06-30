<?php

use BeAmado\OjsMigrator\Entity\SubmissionReviewHandler;
use BeAmado\OjsMigrator\Entity\SubmissionHandler;
use BeAmado\OjsMigrator\Test\FunctionalTest;
use BeAmado\OjsMigrator\Test\StubInterface;
use BeAmado\OjsMigrator\Test\TestStub;
use BeAmado\OjsMigrator\Registry;
use BeAmado\OjsMigrator\Test\FixtureHandler;

// mocks
use BeAmado\OjsMigrator\Test\SubmissionMock;
use BeAmado\OjsMigrator\Test\ReviewFormMock;

class SubmissionReviewHandlerTest extends FunctionalTest 
                                  implements StubInterface
{
    public static function setUpBeforeClass($args = [
        'createTables' => [
            'submissions',
            'review_forms',
            'review_form_elements',
            'review_form_settings',
            'review_form_elements',
            'review_form_element_settings',
            'review_rounds',
            'review_assignments',
            'review_form_responses',
        ],
    ]) : void {
        parent::setUpBeforeClass($args);
        (new FixtureHandler())->createSeveral([
            'journals' => [
                'test_journal',
            ],
            'users' => [
                'ironman',
                'hulk',
            ],
            'sections' => [
                'sports',
                'sciences',
            ],
        ]);

        Registry::get('ReviewFormHandler')->import(
            (new ReviewFormMock())->getFirstReviewForm()
        );
    }

    public function getStub()
    {
        return new class extends SubmissionReviewHandler {
            use TestStub;
        };
    }

    protected function getSmHrStub()
    {
        return new class extends SubmissionHandler {
            use TestStub;
        };
    }

    protected function dataMapper()
    {
        return Registry::get('DataMapper');
    }

    protected function createRWC2015()
    {
        return (new SubmissionMock())->getRWC2015();
    }

    public function testCanImportAReviewRound()
    {
        $rwc2015 = $this->createRWC2015();
        $this->getStub()->createOrUpdateInDatabase($rwc2015);

        $imported = Registry::get('SubmissionReviewHandler')->importReviewRound(
            $rwc2015->get('review_rounds')->get(0)
        );

        $this->assertSame(
            '1',
            implode('-', [
                (int) $imported,
            ])
        );
    }

    /**
     * @depends testCanImportAReviewRound
     */
    public function testCanGetTheReviewRounds()
    {
        $rwc2015 = $this->createRWC2015();
        
        $rounds = Registry::get('SubmissionReviewHandler')->getReviewRounds(
            $this->dataMapper()->getMapping(
                $this->getSmHrStub()->formTableName(),
                $rwc2015->getId()
            )
        );

        $this->assertSame(
            '1',
            implode('-', [
                $rounds->length(),
            ])
        );
    }

    public function testCanImportAReviewFormResponse()
    {
        $eh = Registry::get('EntityHandler');
        
        $rwc2015 = $this->createRWC2015();
        $this->getSmHrStub()->callMethod('registerSubmission', $rwc2015);

        $review = $eh->getValidData('review_assignments', [
            'review_id' => 888,
            'submission_id' => 9093,
            'reviewer_id' => 3930933,
        ]);
        $eh->createOrUpdateInDatabase($review);

        $rfr = Registry::get('MemoryManager')->create([
            'review_id' => $review->getId(),
            'review_form_element_id' => 28,
            'response_type' => 'string',
            'response_value' => 'lalala',
        ]);

        $imported = $this->getStub()->callMethod(
            'importReviewFormResponse',
            $rfr
        );

        $responses = Registry::get('ReviewFormResponsesDAO')->read([
            'review_id' => $this->dataMapper()->getMapping(
                'review_assignments',
                $review->getId()
            )
        ]);

        $this->assertSame(
            '1-1-1-1',
            implode('-', [
                (int) $imported,
                $responses->length(),
                (int) $this->areEqual(
                    $rfr->get('response_type')->getValue(),
                    $responses->get(0)->getData('response_type')
                ),
                (int) $this->areEqual(
                    $rfr->get('response_value')->getValue(),
                    $responses->get(0)->getData('response_value')
                ),
            ])
        );

    }

    /**
     * @depends testCanImportAReviewFormResponse
     * @depends testCanImportAReviewRound
     */
    public function testCanImportAReviewAssignment()
    {
        $rwc2015 = $this->createRWC2015();
        $review = $rwc2015->get('review_assignments')->get(0);

        $imported = Registry::get('SubmissionReviewHandler')->importReviewAssignment(
            $review
        );

        $reviewId = $this->dataMapper()->getMapping(
            'review_assignments',
            $review->get('review_id')->getValue()
        );

        $responses = Registry::get('ReviewFormResponsesDAO')->read([
            'review_id' => $reviewId,
        ]);

        $this->assertSame(
            '1-0-1-1',
            implode('-', [
                (int) $imported,
                (int) is_null($responses),
                $responses->length(),
                (int) $this->areEqual(
                    $review->get('responses')->get(0)->get('response_value')->getValue(),
                    $responses->get(0)->getData('response_value')
                ),
            ])
        );
    }

    /**
     * @depends testCanImportAReviewAssignment
     */
    public function testCanGetTheReviewAssignments()
    {
        $revs = Registry::get('SubmissionReviewHandler')->getReviewAssignments(
            $this->dataMapper()->getMapping(
                $this->getSmHrStub()->formTableName(),
                $this->createRWC2015()->getId()
            )
        );

        $this->assertSame(
            '1-1',
            implode('-', [
                $revs->length(),
                $revs->get(0)->get('responses')->length(),
            ])
        );
    }
}
