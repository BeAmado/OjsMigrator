<?php

return array(
    '__tableName_' => 'users',
    'user_id' => 9725,
    'username' => 'thor',
    'password' => 'thunderhighinthemountain',
    'first_name' => 'Donald',
    'last_name' => 'Blake',
    'email' => 'thor@avengers.com',
    'roles' => array(
        array(
            '__tableName_' => 'roles',
            'role_id' => 256,
            'journal_id' => '[test_journal]',
            'user_id' => 9725,
        ),
        array(
            '__tableName_' => 'roles',
            'role_id' => 4096,
            'journal_id' => '[test_journal]',
            'user_id' => 9725,
        ),
    ),
    'settings' => array(
        array(
            '__tableName_' => 'user_settings',
            'user_id' => 9725,
            'locale' => 'pt_BR',
            'setting_name' => 'affiliation',
            'setting_value' => 'Avengers',
            'setting_type' => 'string',
        ),
    ),
    'interests' => array(
        array(
            '__tableName_' => 'user_interests',
            'user_id' => 9725,
            'controlled_vocab_entry_id' => 83,
            'controlled_vocab_entries' => array(
                array(
                    '__tableName_' => 'controlled_vocab_entries',
                    'controlled_vocab_entry_id' => 83,
                    'controlled_vocab_id' => 856,
                    'seq' => 0,
                    'settings' => array(
                        array(
                            '__tableName_' => 'controlled_vocab_entry_settings',
                            'controlled_vocab_entry_id' => 83,
                            'locale' => 'en',
                            'setting_name' => 'interest',
                            'setting_value' => 'fighting',
                            'setting_type' => 'string',
                        ),
                    ),
                    'controlled_vocabs' => array(
                        array(
                            '__tableName_' => 'controlled_vocabs',
                            'controlled_vocab_id' => 856,
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
            'user_id' => 9725,
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
