<?php

return array(
    '__tableName_' => 'groups',
    'group_id' => 782,
    'context' => 1,
    'assoc_id' => '[test_journal_id]',
    'about_displayed' => 1,
    'seq' => 1,
    'assoc_type' => 256,
    'publish_email' => 0,
    'settings' => array(
        array(
            '__tableName_' => 'group_settings',
            'group_id' => 782,
            'locale' => 'en',
            'setting_name' => 'title',
            'setting_value' => 'forwards',
            'setting_type' => 'string',
        ),
    ),
    'memberships' => array(
        array(
            '__tableName_' => 'group_memberships',
            'group_id' => 782,
            'user_id' => '[hulk_user_id]',
            'about_displayed' => 1,
            'seq' => 1,
        ),
        array(
            '__tableName_' => 'group_memberships',
            'group_id' => 782,
            'user_id' => '[thor_user_id]',
            'about_displayed' => 1,
            'seq' => 2,
        ),
    ),
);
