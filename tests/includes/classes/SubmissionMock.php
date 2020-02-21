<?php

namespace BeAmado\OjsMigrator;

class SubmissionMock extends EntityMock
{
    use JournalFiller;
    use UserFiller;
    use SectionFiller;

    public function __construct($name = null)
    {
        parent::__construct('submissions');
    }

    protected function fillSettings($settings)
    {
    }

    protected function fillFiles($files)
    {
    }

    protected function fillSupplementaryFiles($suppFiles)
    {
    }

    protected function fillGalleys($galleys)
    {
    }

    protected function fillComments($comments)
    {
    }

    protected function fillKeywords($keywords)
    {
    }

    protected function fillAuthors($authors)
    {
    }

    protected function fillEditAssignments($edits)
    {
    }

    protected function fillEditDecisions($edits)
    {
    }

    protected function fillHistory($history)
    {
    }

    protected function fillReview($reviews)
    {
    }
}
