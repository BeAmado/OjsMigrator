<?php

namespace BeAmado\OjsMigrator\Entity;
use \BeAmado\OjsMigrator\Registry;

class SubmissionCommentHandler extends EntityHandler
{
    protected function smHr()
    {
        return Registry::get('SubmissionHandler');
    }

    public function create($data, $extra = null)
    {
        return $this->getValidData(
            $this->smHr()->formTableName('comments'),
            $data
        );
    }

    protected function types()
    {
        return array(
            'peer_review' => 0x01,
            'editor_decision' => 0x02,
            'copyedit' => 0x03,
            'layout' => 0x04,
            'proofread' => 0x05,
        );
    }

    protected function getDataCommentType($data)
    {
        if (\is_array($data) && \array_key_exists('comment_type', $data))
            return $data['comment_type'];

        if ($this->isMyObject($data) && $data->hasAttribute('comment_type'))
            return $data->get('comment_type')->getValue();
    }

    protected function commentTypeIs($type, $data)
    {
        if (!\array_key_exists(
            \strtolower($type), 
            $this->types()
        ))
            return;

        return $this->types()[$type] == $this->getDataCommentType($data);
    }

    public function typeIsPeerReview($data)
    {
        return $this->commentTypeIs('peer_review', $data);
    }

    protected function getAssocTable($data)
    {
        return $this->typeIsPeerReview($data)
            ? 'review_assignments'
            : $this->smHr()->formTableName();
    }

    protected function assocIsSubmission($comment)
    {
        return !$this->typeIsPeerReview($comment) &&
            $comment->getData('assoc_id') == $comment->getData(
                $this->smHr()->formIdField()
            );
    }

    protected function setMappedComment($comment)
    {
        if (
            !$this->typeIsPeerReview($comment) &&
            !$this->assocIsSubmission($comment)
        )
            return $this->setMappedData($comment, array(
                $this->smHr()->formTableName() => $this->smHr()->formIdField(),
                'users' => 'author_id',
            ));

        return $this->setMappedData($comment, array(
            'users' => 'author_id',
            $this->smHr()->formTableName() => $this->smHr()->formIdField(),
        )) && $this->setMappedData($comment, array(
            $this->getAssocTable($comment) => 'assoc_id',
        ));
    }

    public function importSubmissionComment($comment)
    {
        if (!$this->isEntity($comment))
            return $this->importSubmissionComment($this->create($comment));

        if (!$this->setMappedComment($comment))
            return false;

        return $this->createOrUpdateInDatabase($comment);
    }

    public function importComments($submission)
    {
        if (
            $submission->hasAttribute('comments') &&
            $this->isMyObject($submission->get('comments'))
        )
            return $submission->get('comments')->forEachValue(function($cmt) {
                return $this->importSubmissionComment($cmt);
            });
    }

    public function getSubmissionComments($submissionId)
    {
        return $this->smHr()->getEntityDAO(
            $this->smHr()->formTableName('comments')
        )->read(array(
            $this->smHr()->formIdField() => $submissionId,
        ));
    }

}
