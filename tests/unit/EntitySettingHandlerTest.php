<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Entity\EntitySettingHandler;
use BeAmado\OjsMigrator\Test\StubInterface;
use BeAmado\OjsMigrator\Test\TestStub;
use BeAmado\OjsMigrator\Registry;

class EntitySettingHandlerTest extends TestCase implements StubInterface
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

    public function testCanCheckIfTheSettingValueIsCorrectlySerialized()
    {
        $data = [
            'good' => [
                $this->createEntity('journal_settings', [
                    'journal_id' => 4,
                    'setting_name' => 'albums',
                    'setting_type' => 'object',
                    'setting_value' => serialize([
                        'No Gravity',
                        'Universo Inverso',
                        'Fullblast',
                        'Sounds of Innocence',
                    ])
                ]),
                $this->createEntity('journal_settings', [
                    'journal_id' => 4,
                    'setting_name' => 'No Gravity songs',
                    'setting_type' => 'object',
                    'setting_value' => 'a:12:{'
                        . 'i:0;s:7:"Enfermo";'
                        . 'i:1;s:18:"Endangered Species";'
                        . 'i:2;s:8:"Escaping";'
                        . 'i:3;s:10:"No Gravity";'
                        . 'i:4;s:12:"Pau-de-Arara";'
                        . 'i:5;s:18:"La force de l\'Âme";'
                        . 'i:6;s:32:"Tapping into my dark tranquility";'
                        . 'i:7;s:15:"Moment of truth";'
                        . 'i:8;s:18:"Beautiful language";'
                        . 'i:9;s:7:"Dilemma";'
                        . 'i:10;s:16:"Feliz Desilusão";'
                        . 'i:11;s:17:"Choro de criança";'
                        . '}',
                ]),
            ],
            'bad' => [
                $this->createEntity('journal_settings', [
                    'journal_id' => 4,
                    'setting_name' => 'No Gravity songs',
                    'setting_type' => 'object',
                    'setting_value' => 'a:12:{'
                        . 'i:0;s:7:"Enfermo";'
                        . 'i:1;s:18:"Endangered Species";'
                        . 'i:2;s:8:"Escaping";'
                        . 'i:3;s:10:"No Gravity";'
                        . 'i:4;s:12:"Pau-de-Arara";'
                        . 'i:5;s:17:"La force de l\'Âme";'
                        . 'i:6;s:32:"Tapping into my dark tranquility";'
                        . 'i:7;s:15:"Moment of truth";'
                        . 'i:8;s:18:"Beautiful language";'
                        . 'i:9;s:7:"Dilemma";'
                        . 'i:10;s:15:"Feliz Desilusão";'
                        . 'i:11;s:16:"Choro de criança";'
                        . '}',
                ]),
            ],
        ];

        $this->assertSame(
            '1|1|',
            implode('|', array_merge(
                array_map(
                    [$this->settingHandler(), 'objectSettingOk'],
                    $data['good']
                ),
                array_map(
                    [$this->settingHandler(), 'objectSettingOk'],
                    $data['bad']
                )
            ))
        );

    }

    public function testCanFixTheSettingObjectValue()
    {
        $data = [
            'good' => [
                $this->createEntity('journal_settings', [
                    'journal_id' => 4,
                    'setting_name' => 'albums',
                    'setting_type' => 'object',
                    'setting_value' => serialize([
                        'No Gravity',
                        'Universo Inverso',
                        'Fullblast',
                        'Sounds of Innocence',
                    ])
                ]),
                $this->createEntity('journal_settings', [
                    'journal_id' => 4,
                    'setting_name' => 'No Gravity songs',
                    'setting_type' => 'object',
                    'setting_value' => 'a:12:{'
                        . 'i:0;s:7:"Enfermo";'
                        . 'i:1;s:18:"Endangered Species";'
                        . 'i:2;s:8:"Escaping";'
                        . 'i:3;s:10:"No Gravity";'
                        . 'i:4;s:12:"Pau-de-Arara";'
                        . 'i:5;s:18:"La force de l\'Âme";'
                        . 'i:6;s:32:"Tapping into my dark tranquility";'
                        . 'i:7;s:15:"Moment of truth";'
                        . 'i:8;s:18:"Beautiful language";'
                        . 'i:9;s:7:"Dilemma";'
                        . 'i:10;s:16:"Feliz Desilusão";'
                        . 'i:11;s:17:"Choro de criança";'
                        . '}',
                ]),
            ],
            'bad' => [
                $this->createEntity('journal_settings', [
                    'journal_id' => 4,
                    'setting_name' => 'No Gravity songs',
                    'setting_type' => 'object',
                    'setting_value' => 'a:12:{'
                        . 'i:0;s:7:"Enfermo";'
                        . 'i:1;s:18:"Endangered Species";'
                        . 'i:2;s:8:"Escaping";'
                        . 'i:3;s:10:"No Gravity";'
                        . 'i:4;s:12:"Pau-de-Arara";'
                        . 'i:5;s:17:"La force de l\'Âme";'
                        . 'i:6;s:32:"Tapping into my dark tranquility";'
                        . 'i:7;s:15:"Moment of truth";'
                        . 'i:8;s:18:"Beautiful language";'
                        . 'i:9;s:7:"Dilemma";'
                        . 'i:10;s:15:"Feliz Desilusão";'
                        . 'i:11;s:16:"Choro de criança";'
                        . '}',
                ]),
            ],
        ];

        $this->settingHandler()->fixObjectSettingValue($data['bad'][0]);

        $this->assertTrue(Registry::get('ArrayHandler')->equals(
            unserialize($data['good'][1]->getData('setting_value')),
            unserialize($data['bad'][0]->getData('setting_value'))
        ));
    }
}
