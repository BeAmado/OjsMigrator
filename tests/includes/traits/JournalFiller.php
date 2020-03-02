<?php

namespace BeAmado\OjsMigrator\Test;

trait JournalFiller
{
    /**
     * Fills the entity journal_id field with the proper id.
     *
     * @param \BeAmado\OjsMigrator\MyObject
     * @param string $field
     * @return \BeAmado\OjsMigrator\MyObject 
     */
    public function fillJournalId($entity, $field = 'journal_id')
    {
        if (
            !\is_a($entity, \BeAmado\OjsMigrator\MyObject::class) ||
            !$entity->hasAttribute($field)
        )
            return false;

        $journal = (new JournalMock())->getJournal(\str_replace(
            array('[', '_id', ']'),
            '', 
            $entity->get($field)->getValue()
        ));

        if (
            !\is_a($journal, \BeAmado\OjsMigrator\MyObject::class) ||
            !$journal->hasAttribute('journal_id')
        )
            return false;

        $entity->set(
            $field,
            $journal->get('journal_id')->getValue()
        );

        return $entity;
    }
}
