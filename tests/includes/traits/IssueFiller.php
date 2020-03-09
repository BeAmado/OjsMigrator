<?php

namespace BeAmado\OjsMigrator\Test;

trait IssueFiller
{
    /**
     * Fills the entity issue_id field with the proper id.
     *
     * @param \BeAmado\OjsMigrator\MyObject
     * @param string $field
     * @return \BeAmado\OjsMigrator\MyObject 
     */
    public function fillIssueId($entity, $field = 'issue_id')
    {
        if (
            !\is_a($entity, \BeAmado\OjsMigrator\MyObject::class) ||
            !$entity->hasAttribute($field)
        )
            return false;

        $issue = (new IssueMock())->getIssue(\str_replace(
            array('[', '_issue', '_id', ']'),
            '', 
            $entity->get($field)->getValue()
        ));

        if (
            !\is_a($issue, \BeAmado\OjsMigrator\MyObject::class) ||
            !$issue->hasAttribute('issue_id')
        )
            return false;

        $entity->set(
            $field,
            $issue->get('issue_id')->getValue()
        );

        return $entity;
    }
}
