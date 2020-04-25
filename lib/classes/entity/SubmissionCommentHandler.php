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

    public function commentTypeIsPeerReview($data)
    {
        return $this->commentTypeIs('peer_review', $data);
    }

    protected function getAssocTable($data)
    {
        return $this->commentTypeIsPeerReview($data)
            ? 'review_assignments'
            : $this->smHr()->formTableName();
    }

    protected function assocIsSubmission($comment)
    {
        return !$this->commentTypeIsPeerReview($comment) &&
            $comment->getData('assoc_id') == $comment->getData(
                $this->smHr()->formIdField()
            );
    }

    protected function setMappedComment($comment)
    {
        if ($this->assocIsSubmission($comment))
            return $this->setMappedData($comment, array(
                $this->smHr()->formTableName() => 'assoc_id',
                'users' => 'author_id',
            )) && ($comment->set(
                $this->smHr()->formIdField(),
                $comment->getData('assoc_id')
            ) || true);
        else if ($this->commentTypeIsPeerReview($comment))
            return $this->setMappedData($comment, array(
                $this->smHr()->formTableName() => $this->smHr()->formIdField(),
                'users' => 'author_id',
                'review_assignments' => 'assoc_id',
            ));

        return $this->setMappedData($comment, array(
            $this->smHr()->formTableName() => $this->smHr()->formIdField(),
            'users' => 'author_id',
        ));
    }

    public function importSubmissionComment($comment)
    {
        if (!$this->isEntity($comment))
            return $this->importSubmissionComment($this->create($comment));

        if (!$this->setMappedComment($comment))
            return false;

        return $this->createOrUpdateInDatabase($comment);
//        return $this->importEntity(
//            $data,
//            $this->smHr()->formTableName('comments'),
//            array(
//                $this->smHr()->formTableName() => $this->smHr()->formIdField(),
//                'users' => 'author_id',
//                $this->getAssocTable($data) => 'assoc_id',
//            )
//        );
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
