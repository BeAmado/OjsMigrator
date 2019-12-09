<?php

use BeAmado\OjsMigrator\FunctionalTest;
use BeAmado\OjsMigrator\Entity\AnnouncementHandler;
use BeAmado\OjsMigrator\Registry;

// interfaces
use BeAmado\OjsMigrator\StubInterface;

// traits
use BeAmado\OjsMigrator\TestStub;

class AnnouncementHandlerTest
{
    protected function createTestJournal()
    {
        return Registry::get('EntityHandler')->create('journals', array(
            'journal_id' => 278,
            'path' => 'test_journal',
        ));
    }

    public function __construct()
    {
        parent::__construct();
        foreach (array(
            'announcements',
            'announcement_settings',
            'announcement_types',
            'announcement_type_settings',
            'journals',
        ) as $table) {
            Registry::get('DbHandler')->createTableIfNotExists($table)
        }
    }

    public function getStub()
    {
        return new class extends AnnouncementHandler {
            use TestStub;
        };
    }

    public function testCanRegisterAnAnnouncement()
    {
        $ann = Registry::get('AnnouncementHandler')->create(array(
            'announcement_id' => 123,
            'assoc_id' => 
        ));

        $this->assertTrue(
            $this->getStub()->callMethod(
                'registerAnnouncement',
                $ann
            )
        );
    }
}
