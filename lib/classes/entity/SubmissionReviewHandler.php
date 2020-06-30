<?php

namespace BeAmado\OjsMigrator\Entity;
use \BeAmado\OjsMigrator\Registry;

class SubmissionReviewHandler extends EntityHandler
{
    protected function smHr()
    {
        return Registry::get('SubmissionHandler');
    }

    public function importReviewRound($data)
    {
        return $this->importEntity(
            $data,
            'review_rounds',
            array($this->smHr()->formTableName() => 'submission_id')
        );
    }

    public function importReviewAssignment($data)
    {
        
        $imported =  $this->importEntity(
            $data,
            'review_assignments',
            array(
                $this->smHr()->formTableName() => 'submission_id',
                'users' => 'reviewer_id',
                $this->smHr()->formTableName('files') => 'reviewer_file_id',
                'review_forms' => 'review_form_id',
                'review_rounds' => 'review_round_id',
            )
        );
        return $data->hasAttribute('responses')
            ? $data->get('responses')->forEachValue(function($response) {
                return $this->importReviewFormResponse($response);
            })
            : true;
    }

    protected function importReviewFormResponse($data)
    {
        return $this->importEntity(
            $data,
            'review_form_responses',
            array(
                'review_assignments' => 'review_id',
                'review_form_elements' => 'review_form_element_id',
            )
        );
    }

    protected function getReviewFormResponses($reviewId)
    {
        return Registry::get('ReviewFormResponsesDAO')->read(array(
            'review_id' => $reviewId,
        ));
    }

    public function getReviewRounds($submission)
    {
        return Registry::get('ReviewRoundsDAO')->read(array(
            'submission_id' => $this->smHr()->getSubmissionId($submission),
        ));
    }

    public function getReviewAssignments($submission)
    {
        $reviews = Registry::get('ReviewAssignmentsDAO')->read(array(
            'submission_id' => $this->smHr()->getSubmissionId($submission),
        ));

        if (
            \is_a($reviews, \BeAmado\OjsMigrator\MyObject::class) &&
            $reviews->length() > 0
        )
            $reviews->forEachValue(function($review) {
                $review->set(
                    'responses',
                    $this->getReviewFormResponses($review->getId())
                );
            });

        return $reviews;
    }
}
