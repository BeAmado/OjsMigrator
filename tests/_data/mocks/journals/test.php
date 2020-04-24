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
