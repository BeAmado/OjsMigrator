<?php

return array(
    '__tableName_' => 'users',
    'user_id' => 8273,
    'username' => 'greenlatern',
    'password' => 'beware green lanterns light',
    'first_name' => 'Hal',
    'last_name' => 'Jordan',
    'email' => 'greeny@justice.com',
    'roles' => array(
        array(
            '__tableName_' => 'roles',
            'role_id' => 4096,
            'journal_id' => 178,
            'user_id' => 8273,
        ),
    ),
    'settings' => array(
        array(
            '__tableName_' => 'user_settings',
            'user_id' => 8273,
            'locale' => 'pt_BR',
            'setting_name' => 'filterEditor',
            'setting_value' => '0',
            'setting_type' => 'int',
        ),
        array(
            '__tableName_' => 'user_settings',
            'user_id' => 8273,
            'locale' => 'pt_BR',
            'setting_name' => 'filterSection',
            'setting_value' => '1',
            'setting_type' => 'int',
        ),
        array(
            '__tableName_' => 'user_settings',
            'user_id' => 8273,
            'locale' => 'pt_BR',
            'setting_name' => 'affiliation',
            'setting_value' => 'Justice League',
            'setting_type' => 'string',
        ),
    ),
    'interests' => array(
        array(
            '__tableName_' => 'user_interests',
            'user_id' => 8273,
            'controlled_vocab_entry_id' => 8723,
            'controlled_vocab_entries' => array(
                array(
                    '__tableName_' => 'controlled_vocab_entries',
                    'controlled_vocab_entry_id' => 8723,
                    'controlled_vocab_id' => 5614,
                    'seq' => 0,
                    'settings' => array(
                        array(
                            '__tableName_' => 'controlled_vocab_entry_settings',
                            'controlled_vocab_entry_id' => 8723,
                            'locale' => 'en',
                            'setting_name' => 'interest',
                            'setting_value' => 'science',
                            'setting_type' => 'string',
                        ),
                    ),
                    'controlled_vocabs' => array(
                        array(
                            '__tableName_' => 'controlled_vocabs',
                            'controlled_vocab_id' => 5614,
                            'symbolic' => 'interest',
                            'assoc_type' => 0,
                            'assoc_id' => 0,
                        ),
                    ),
                ),
            ),
        ),
        array(
            '__tableName_' => 'user_interests',
            'user_id' => 8273,
            'controlled_vocab_entry_id' => 14,
            'controlled_vocab_entries' => array(
                array(
                    '__tableName_' => 'controlled_vocab_entries',
                    'controlled_vocab_entry_id' => 14,
                    'controlled_vocab_id' => 26,
                    'seq' => 0,
                    'settings' => array(
                        array(
                            '__tableName_' => 'controlled_vocab_entry_settings',
                            'controlled_vocab_entry_id' => 14,
                            'locale' => 'en',
                            'setting_name' => 'interest',
                            'setting_value' => 'high tech',
                            'setting_type' => 'string',
                        ),
                    ),
                    'controlled_vocabs' => array(
                        array(
                            '__tableName_' => 'controlled_vocabs',
                            'controlled_vocab_id' => 26,
                            'symbolic' => 'interest',
                            'assoc_type' => 0,
                            'assoc_id' => 0,
                        ),
                    ),
                ),
            ),
        ),
        array(
            '__tableName_' => 'user_interests',
            'user_id' => 8273,
            'controlled_vocab_entry_id' => 3629,
            'controlled_vocab_entries' => array(
                array(
                    '__tableName_' => 'controlled_vocab_entries',
                    'controlled_vocab_entry_id' => 3629,
                    'controlled_vocab_id' => 61,
                    'seq' => 0,
                    'settings' => array(
                        array(
                            '__tableName_' => 'controlled_vocab_entry_settings',
                            'controlled_vocab_entry_id' => 3629,
                            'locale' => 'en',
                            'setting_name' => 'interest',
                            'setting_value' => 'parties',
                            'setting_type' => 'string',
                        ),
                    ),
                    'controlled_vocabs' => array(
                        array(
                            '__tableName_' => 'controlled_vocabs',
                            'controlled_vocab_id' => 61,
                            'symbolic' => 'interest',
                            'assoc_type' => 0,
                            'assoc_id' => 0,
                        ),
                    ),
                ),
            ),
        ),
    ),
);
