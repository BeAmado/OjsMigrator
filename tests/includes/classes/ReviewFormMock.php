<?php

namespace BeAmado\OjsMigrator;

class ReviewFormMock extends EntityMock
{
    public function __construct($name = null)
    {
        parent::__construct('review_forms');
    }

    protected function fillJournalId($rev)
    {
        $journal = (new JournalMock())->getJournal(\str_replace(
            '_id',
            '',
            $this->removeBrackets($rev->get('assoc_id')->getValue()) // remove the []
        ));

        $rev->set(
            'assoc_id',
            $journal->get('journal_id')->getValue()
        );

        return $rev;
    }

    protected function fill($reviewForm)
    {
        return $this->fillJournalId($reviewForm);
    }

    public function getFirstReviewForm()
    {
        return $this->fill($this->get('first'));
    }

    public function getSecondReviewForm()
    {
        return $this->fill($this->get('second'));
    }
}
