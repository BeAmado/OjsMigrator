<?php

namespace BeAmado\OjsMigrator\Test;
use \BeAmado\OjsMigrator\Registry;

class JournalMock extends EntityMock
{
    public function __construct($name = null)
    {
        parent::__construct('journals');
    }

    public function getTestJournal()
    {
        return $this->getJournal('test');
    }

    public function getJournal($journal)
    {
        if (\strpos(\strtolower($journal), 'test_journal') !== false)
            return $this->getTestJournal();

        return Registry::get('JournalHandler')->create(parent::get($journal));
    }
}
