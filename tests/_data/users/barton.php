<?php

return array(
    '__tableName_' => 'users',
    'user_id' => 9285,
    'username' => 'hawkeye',
    'password' => 'oneshot',
    'first_name' => 'Clint',
    'last_name' => 'Barton',
    'email' => 'clint@avengers.com',
    'roles' => array(
        array(
            '__tableName_' => 'roles',
            'role_id' => 65536,
            'journal_id' => 178,
            'user_id' => 9285,
        ),
        array(
            '__tableName_' => 'roles',
            'role_id' => 4096,
            'journal_id' => 178,
            'user_id' => 9285,
        ),
    ),
    'settings' => array(
        array(
            '__tableName_' => 'user_settings',
            'user_id' => 9285,
            'locale' => 'pt_BR',
            'setting_name' => 'filterSection',
            'setting_value' => '1',
            'setting_type' => 'int',
        ),
        array(
            '__tableName_' => 'user_settings',
            'user_id' => 9285,
            'locale' => 'pt_BR',
            'setting_name' => 'orcid',
            'setting_value' => 'http://orcid.org/0260-0061-0502-0023',
            'setting_type' => 'string',
        ),
        array(
            '__tableName_' => 'user_settings',
            'user_id' => 9285,
            'locale' => 'pt_BR',
            'setting_name' => 'affiliation',
            'setting_value' => 'Avengers',
            'setting_type' => 'string',
        ),
    ),
    'interests' => array(
        array(
            '__tableName_' => 'user_interests',
            'user_id' => 9285,
            'controlled_vocab_entry_id' => 8,
            'controlled_vocab_entries' => array(
                array(
                    '__tableName_' => 'controlled_vocab_entries',
                    'controlled_vocab_entry_id' => 8,
                    'controlled_vocab_id' => 514,
                    'seq' => 0,
                    'settings' => array(
                        array(
                            '__tableName_' => 'controlled_vocab_entry_settings',
                            'controlled_vocab_entry_id' => 8,
                            'locale' => 'en',
                            'setting_name' => 'interest',
                            'setting_value' => 'accuracy',
                            'setting_type' => 'string',
                        ),
                    ),
                    'controlled_vocabs' => array(
                        array(
                            '__tableName_' => 'controlled_vocabs',
                            'controlled_vocab_id' => 514,
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
            'user_id' => 9285,
            'controlled_vocab_entry_id' => 2214,
            'controlled_vocab_entries' => array(
                array(
                    '__tableName_' => 'controlled_vocab_entries',
                    'controlled_vocab_entry_id' => 2214,
                    'controlled_vocab_id' => 2698,
                    'seq' => 0,
                    'settings' => array(
                        array(
                            '__tableName_' => 'controlled_vocab_entry_settings',
                            'controlled_vocab_entry_id' => 2214,
                            'locale' => 'en',
                            'setting_name' => 'interest',
                            'setting_value' => 'stealth',
                            'setting_type' => 'string',
                        ),
                    ),
                    'controlled_vocabs' => array(
                        array(
                            '__tableName_' => 'controlled_vocabs',
                            'controlled_vocab_id' => 2698,
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
