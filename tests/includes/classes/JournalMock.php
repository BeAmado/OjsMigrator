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
        return Registry::get('EntityHandler')->create('journals', array(
            'journal_id' => 179,
            'path' => 'test_journal',
        ));
    }
}
