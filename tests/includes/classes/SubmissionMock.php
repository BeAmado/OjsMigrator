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
                $this->basicFill($s);
                if ($s->hasAttribute('settings'))
                    $s->get('settings')->forEachValue(function($setting) {
                        $this->basicFill($setting);
                    });
            });
    }

    protected function fillGalleys($submission)
    {
        if ($submission->hasAttribute('galleys'))
            $submission->get('galleys')->forEachValue(function($galley) {
                $this->basicFill($galley);
                if ($galley->hasAttribute('settings'))
                    $galley->get('settings')->forEachValue(function($setting) {
                        $this->basicFill($setting);
                    });
            });
    }

    protected function fillComments($submission)
    {
        if ($submission->hasAttribute('comments'))
            $submission->get('comments')->forEachValue(function($comment) {
                $this->basicFill($comment);
                $this->fillUserId($comment, 'author_id');
            });
    }

    protected function fillEditAssignments($submission)
    {
        if ($submission->hasAttribute('edit_assignments'))
            $submission->get('edit_assignments')->forEachValue(function($ea) {
                $this->setSubmissionIdField($ea);
                $this->fillUserId($ea, 'editor_id');
            });
    }

    protected function fillEditDecisions($submission)
    {
        if ($submission->hasAttribute('edit_decisions'))
            $submission->get('edit_decisions')->forEachValue(function($ed) {
                $this->setSubmissionIdField($ed);
                $this->fillUserId($ed, 'editor_id');
            });
    }

    protected function fillReviews($submission)
    {
        if ($submission->hasAttribute('review_assignments'))
            $submission->get('review_assignments')->forEachValue(function($r) {
                $this->fillUserId($r, 'reviewer_id');
            });
    }

    protected function fillKeywords($keywords)
    {
    }

    protected function fillHistory($history)
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

        $this->fillSupplementaryFiles($submission);
        $this->fillGalleys($submission);
        $this->fillComments($submission);
        $this->fillEditAssignments($submission);
        $this->fillEditDecisions($submission);
        $this->fillReviews($submission);

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
