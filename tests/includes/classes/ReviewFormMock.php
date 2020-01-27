<?php

namespace BeAmado\OjsMigrator;

class ReviewFormMock extends EntityMock
{
    use JournalFiller; // trait to fill the journal_id

    public function __construct($name = null)
    {
        parent::__construct('review_forms');
    }

    protected function fill($reviewForm)
    {
        return $this->fillJournalId($reviewForm, 'assoc_id');
    }

    public function getReviewForm($name)
    {
        return Registry::get('ReviewFormHandler')->create(
            $this->fill($this->get($name))
        );
    }

    public function getFirstReviewForm()
    {
        return $this->getReviewForm('first');
    }

    public function getSecondReviewForm()
    {
        return $this->getReviewForm('second');
    }
}
