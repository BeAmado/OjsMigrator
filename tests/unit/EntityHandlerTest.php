<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\EntityHandler;
use BeAmado\OjsMigrator\Registry;

class EntityHandlerTest extends TestCase
{
    public function testCreateJournalPassingNoData()
    {
        $journal = (new EntityHandler())->create('journals');

        $this->assertTrue(
            $journal->getData('journal_id') === null &&
            $journal->getData('path') === null &&
            $journal->getData('seq') === '0' &&
            $journal->getData('enabled') === '1' &&
            $journal->getData('primary_locale') === null
        );
    }

    public function testCreateSectionEditor()
    {
        $sectionEditor = Registry::get('EntityHandler')->create(
            'section_editors',
            array(
                'user_id' => 21,
                'journal_id' => 4,
                'section_id' => 327,
            )
        );

        $this->assertTrue(
            $sectionEditor->getTableName() === 'section_editors' &&
            $sectionEditor->getData('user_id') === 21 &&
            $sectionEditor->getData('section_id') === 327 &&
            $sectionEditor->getData('journal_id') === 4 &&
            $sectionEditor->getData('can_edit') == 1 &&
            $sectionEditor->getData('can_review') == 1
        );
    }
}
