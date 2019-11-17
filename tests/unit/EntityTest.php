<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Entity\Entity;
use BeAmado\OjsMigrator\Registry;

class EntityTest extends TestCase
{
    public function testCanCreateEntityForJournal()
    {
        $entity = new Entity(
            array(
                'journal_id' => 4,
                'path' => 'journal_test',
                'seq' => 0,
                'primary_locale' => 'fr_CA',
                'enabled' => 0,
            ),
            'journals'
        );

        $this->assertSame(
            'fr_CA',
            $entity->getData('primary_locale')
        );
    }

    public function testCanAddSettingsToJournal()
    {
        $entity = new Entity(
            array(
                'journal_id' => 4,
                'path' => 'journal_test',
                'seq' => 0,
                'primary_locale' => 'fr_CA',
                'enabled' => 0,
            ),
            'journals'
        );

        $entity->set(
            'settings', 
            array(
                new Entity(
                    array(
                        'journal_id' => 4,
                        'locale' => 'fr_CA',
                        'setting_name' => 'title',
                        'setting_value' => 'Nature béni',
                        'setting_type' => 'string',
                    ),
                    'journal_settings'
                ),
                new Entity(
                    array(
                        'journal_id' => 4,
                        'locale' => 'en_NZ',
                        'setting_name' => 'title',
                        'setting_value' => 'Blessed nature',
                        'setting_type' => 'string',
                    ),
                    'journal_settings'
                ),
            )
        );

        $entity->get('settings')->push(new Entity(
            array(
                'journal_id' => 4,
                'locale' => 'fr_CA',
                'setting_name' => 'description',
                'setting_value' => 'un point de vue différent par rapport a '
                                 . 'la nature que nous entoure',
                'setting_type' => 'string',
            ),
            'journal_settings'
        ));

        $this->assertCount(3, $entity->getData('settings'));
    }

    public function testGetUserId()
    {
        $stageMaster = new \BeAmado\OjsMigrator\OjsScenarioTester();
        $stageMaster->setUpStage();
        $entity = Registry::get('EntityHandler')->create('users', array(
            'user_id' => 312,
            'first_name' => 'Bruce',
            'last_name' => 'Wayne',
            'email' => 'bruce@batcave.com',
            'password' => 'intheshadow',
        ));

        $this->assertSame(312, $entity->getId());
        $stageMaster->tearDownStage();
    }
}
