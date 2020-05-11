<?php

return array(
    '__tableName_' => 'journals',
    'journal_id' => 179,
    'path' => 'test_journal',
    'seq' => 1,
    'primary_locale' => 'en_NZ',
    'enabled' => 1,
    'settings' => array(
        array(
            '__tableName_' => 'journal_settings',
            'journal_id' => 179,
            'locale' => '',
            'setting_name' => 'allowRegAuthor',
            'setting_value' => '1',
            'setting_type' => 'bool',
        ),
        array(
            '__tableName_' => 'journal_settings',
            'journal_id' => 179,
            'locale' => '',
            'setting_name' => 'allowRegReviewer',
            'setting_value' => '0',
            'setting_type' => 'bool',
        ),
        array(
            '__tableName_' => 'journal_settings',
            'journal_id' => 179,
            'locale' => 'pt_BR',
            'setting_name' => 'madre',
            'setting_value' => 's:5:"mamãe";',
            'setting_type' => 'object',
        ),
        array(
            '__tableName_' => 'journal_settings',
            'journal_id' => 179,
            'locale' => 'pt_BR',
            'setting_name' => 'albums',
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
            'setting_type' => 'object',
        ),
    ),
    'plugins' => array(
        array(
            '__tableName_' => 'plugin_settings',
            'plugin_name' => 'webfeedplugin',
            'locale' => '',
            'journal_id' => 179,
            'setting_name' => 'enabled',
            'setting_value' => '0',
            'setting_type' => 'bool',
        ),
    ),
);
