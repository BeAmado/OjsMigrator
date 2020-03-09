<?php

namespace BeAmado\OjsMigrator\Test;
use \BeAmado\OjsMigrator\Registry;

class SubmissionMock extends EntityMock
{
    use JournalFiller;
    use UserFiller;
    use SectionFiller;
    use IssueFiller;

    public function __construct($name = null)
    {
        parent::__construct('submissions');
    }

    protected function formTableName($entity)
    { 
        return Registry::get('SubmissionHandler')->formTableName(
            \str_replace(
                array('[', ']', '_table'),
                '',
                $entity->get('__tableName_')->getValue()
            )
        );
    }

    protected function fillTableName($entity)
    {
        $entity->set(
            '__tableName_',
            $this->formTableName($entity)
        );
    }

    protected function setSubmissionIdField($entity)
    {
        $entity->set(
            Registry::get('SubmissionHandler')->formIdField(),
            $entity->get('[submission_id]')->getValue()
        );
        $entity->remove('[submission_id]');
    }

    protected function basicFill($entity)
    {
        $this->fillTableName($entity);
        
        if ($entity->hasAttribute('[submission_id]'))
            $this->setSubmissionIdField($entity);
    }

    protected function fillPublished($pub)
    {
        $this->basicFill($pub);
        $pub->set(
            Registry::get('SubmissionHandler')->formIdField('published'),
            $pub->get('[published_submission_id]')->getValue()
        );
        $pub->remove('[published_submission_id]');
        $this->fillIssueId($pub);
    }

    protected function fillSettings($submission)
    {
        if ($submission->hasAttribute('settings'))
            $submission->get('settings')->forEachValue(function($setting) {
                $this->basicFill($setting);
            });
    }

    protected function fillFiles($submission)
    {
        if ($submission->hasAttribute('files'))
            $submission->get('files')->forEachValue(function($file) {
                $this->basicFill($file);
            });
    }

    protected function fillSupplementaryFiles($submission)
    {
        if ($submission->hasAttribute('supplementary_files'))
            $submission->get('supplementary_files')->forEachValue(function($s) {
                
            });
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

    protected function fill($submission)
    {
        $this->basicFill($submission);
        $this->fillUserId($submission);
        $this->fillJournalId($submission);
        $this->fillSectionId($submission);
        $this->fillSettings($submission);
        $this->fillFiles($submission);

        if ($submission->hasAttribute('published'))
            $this->fillPublished($submission->get('published'));

        return $submission;
    }

    public function getSubmission($name)
    {
        return Registry::get('SubmissionHandler')->create(
            $this->fill($this->get($name))
        );
    }

    public function getRWC2015()
    {
        return $this->getSubmission('rugby-worldcup-2015');
    }
}
