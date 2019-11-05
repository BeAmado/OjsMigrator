<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Entity;
use BeAmado\OjsMigrator\Registry;

class EntityTest extends TestCase
{
    public function testCanCreateEntityForJournal()
    {
        $entity = new Entity('journals', array(
            'journal_id' => 4,
            'path' => 'journal_test',
            'seq' => 0,
            'primary_locale' => 'fr_CA',
            'enabled' => 0,
        ));

        $this->assertSame(
            'fr_CA',
            $entity->getData('primary_locale')
        );
    }

    public function testCanAddSettingsToJournal()
    {
        $entity = new Entity('journals', array(
            'journal_id' => 4,
            'path' => 'journal_test',
            'seq' => 0,
            'primary_locale' => 'fr_CA',
            'enabled' => 0,
        ));

        $entity->set(
            'settings', 
            array(
                new Entity('journal_settings', array(
                    'journal_id' => 4,
                    'locale' => 'fr_CA',
                    'setting_name' => 'title',
                    'setting_value' => 'Nature béni',
                    'setting_type' => 'string',
                )),
                new Entity('journal_settings', array(
                    'journal_id' => 4,
                    'locale' => 'en_NZ',
                    'setting_name' => 'title',
                    'setting_value' => 'Blessed nature',
                    'setting_type' => 'string',
                )),
            )
        );

        $entity->get('settings')->push(new Entity('journal_settings', array(
            'journal_id' => 4,
            'locale' => 'fr_CA',
            'setting_name' => 'description',
            'setting_value' => 'un point de vue différent par rapport a la nature que nous entoure',
            'setting_type' => 'string',
        )));

        $this->assertCount(3, $entity->getData('settings'));
    }
}
