<?php

return array(
    '__tableName_' => 'groups',
    'group_id' => 8992,
    'context' => 1,
    'assoc_id' => '[test_journal_id]',
    'about_displayed' => 1,
    'seq' => 2,
    'assoc_type' => 256,
    'publish_email' => 1,
    'settings' => array(
        array(
            '__tableName_' => 'group_settings',
            'group_id' => 8992,
            'locale' => 'en',
            'setting_name' => 'title',
            'setting_value' => 'backs',
            'setting_type' => 'string',
        ),
    ),
    'memberships' => array(
        array(
            '__tableName_' => 'group_memberships',
            'group_id' => 8992,
            'user_id' => '[ironman_user_id]',
            'about_displayed' => 1,
            'seq' => 1,
        ),
        array(
            '__tableName_' => 'group_memberships',
            'group_id' => 8992,
            'user_id' => '[batman_user_id]',
            'about_displayed' => 1,
            'seq' => 3,
        ),
        array(
            '__tableName_' => 'group_memberships',
            'group_id' => 8992,
            'user_id' => '[hawkeye_user_id]',
            'about_displayed' => 1,
            'seq' => 4,
        ),
        array(
            '__tableName_' => 'group_memberships',
            'group_id' => 8992,
            'user_id' => '[greenlantern_user_id]',
            'about_displayed' => 1,
            'seq' => 2,
        ),
    ),
);
