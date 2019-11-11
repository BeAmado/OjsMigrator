<?php

use BeAmado\OjsMigrator\FunctionalTest;
use BeAmado\OjsMigrator\EntityHandler;
use BeAmado\OjsMigrator\Registry;

class EntityHandlerTest extends FunctionalTest
{
    public function testCreateJournalPassingNoData()
    {
        $journal = (new EntityHandler())->create('journals');

        $this->assertTrue(
            $journal->getData('journal_id') === null &&
            $journal->getData('path') === '' &&
            $journal->getData('seq') === '0' &&
            $journal->getData('enabled') === '1' &&
            $journal->getData('primary_locale') === ''
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

    public function testComparing2EntitiesFromDifferentTablesReturnsFalse()
    {
        $user = Registry::get('EntityHandler')->create('users', array(
            'username' => 'johndoe',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'johndoe@devnull.com',
            'password' => 'nobody',
        ));

        $userInterest = Registry::get('EntityHandler')->create(
            'user_interests',
            array(
                'user_id' => 13,
                'controlled_vocab_entry_id' => 22,
            )
        );

        $this->assertFalse(
            Registry::get('entityHandler')->areEqual($user, $userInterest)
        );
    }

    public function testComparing2UsersWithDifferentIdsReturnsTrue()
    {
        $user = array(
            'username' => 'johndoe',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'johndoe@devnull.com',
            'password' => 'nobody',
        );

        $user1 = Registry::get('entityhandler')->create('users', $user);
        $user2 = Registry::get('entityhandler')->create('users', $user);

        $user1->set('user_id', 12);
        $user1->set('alpha', 123);

        $user2->set('user_id', 1890);
        $user2->set('alpha', 'clouds');
        $user2->set('beta', 8910);

        $this->assertTrue(
            $user1->getData('user_id') !== $user2->getData('user_id') &&
            Registry::get('EntityHandler')->areEqual($user1, $user2)
        );
    }
}
