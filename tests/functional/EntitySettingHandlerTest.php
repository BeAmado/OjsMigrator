<?php

use BeAmado\OjsMigrator\Entity\EntitySettingHandler;
use BeAmado\OjsMigrator\Test\FunctionalTest;
use BeAmado\OjsMigrator\Test\StubInterface;
use BeAmado\OjsMigrator\Test\TestStub;
use BeAmado\OjsMigrator\Registry;

class EntitySettingHandlerTest extends FunctionalTest implements StubInterface
{
    public function getStub()
    {
        return new class extends EntitySettingHandler { use TestStub; };
    }

    protected function createEntity($name, $data)
    {
        return new \BeAmado\OjsMigrator\Entity\Entity($data, $name);
    }

    protected function settingHandler()
    {
        return Registry::get('EntitySettingHandler');
    }

    public function testCanSeeIfTheTableNameOfTheObjectIsASetting()
    {
        $this->assertSame(
            '1-0-1-0-0',
            implode('-', array_map(function($element) {
                return (int) $this->getStub()->callMethod(
                    'tableNameIsSettings',
                    $element
                );
            }, [
                'journal_settings',
                'just_setting',
                'submission_supp_file_settings',
                'users',
                'nothung'
            ]))
        );
    }

    public function testCanSeeIfTheSettingHasTypeObject()
    {
        $journalSetting = $this->createEntity('journal_settings', [
            'journal_id' => 188,
            'setting_name' => 'json_data',
            'setting_value' => '{"key":"value"}',
            'setting_type' => 'object',
        ]);
        $siteSetting = $this->createEntity('site_settings', [
            'setting_name' => 'title',
            'setting_value' => 'Our perfect site',
            'setting_type' => 'string',
        ]);
        $user = $this->createEntity('users', [
            'username' => 'johndoe',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
        $anything = '2 + 5 = sqrt(49)';
        $this->assertSame(
            '1-0-0-0',
            implode('-', [
                (int) $this->settingHandler()->settingHasTypeObject(
                    $journalSetting
                ),
                (int) $this->settingHandler()->settingHasTypeObject(
                    $user
                ),
                (int) $this->settingHandler()->settingHasTypeObject(
                    $siteSetting
                ),
                (int) $this->settingHandler()->settingHasTypeObject(
                    $anything
                ),
            ])
        );
    }

}
