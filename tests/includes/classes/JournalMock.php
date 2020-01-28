<?php

namespace BeAmado\OjsMigrator;

class JournalMock extends EntityMock
{
    public function __construct($name = null)
    {
        parent::__construct('journals');
    }

    public function getTestJournal()
    {
        return Registry::get('JournalHandler')->create(array(
            'journal_id' => 179,
            'path' => 'test_journal',
        ));
    }

    public function getJournal($journal)
    {
        if (\strpos(\strtolower($journal), 'test_journal') !== false)
            return $this->getTestJournal();

        return parent::get($journal);
    }
}
