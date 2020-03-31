<?php

return array(
    '__tableName_' => 'users',
    'user_id' => 7823,
    'username' => 'hulk',
    'password' => 'roooooaarrrrrrrr!!!!!',
    'first_name' => 'Bruce',
    'last_name' => 'Banner',
    'email' => 'bruce@avengers.com',
    'roles' => array(
        array(
            '__tableName_' => 'roles',
            'role_id' => 16,
            'journal_id' => '[test_journal_id]',
            'user_id' => 7823,
        ),
        array(
            '__tableName_' => 'roles',
            'role_id' => 256,
            'journal_id' => '[test_journal_id]',
            'user_id' => 7823, 
        ),
    ),
    'settings' => array(
        array(
            '__tableName_' => 'user_settings',
            'user_id' => 167,
            'locale' => 'pt_BR',
            'setting_name' => 'filterEditor',
            'setting_value' => '0',
            'setting_type' => 'int',
        ),
        array(
            '__tableName_' => 'user_settings',
            'user_id' => 167,
            'locale' => 'pt_BR',
            'setting_name' => 'filterSection',
            'setting_value' => '1',
            'setting_type' => 'int',
        ),
        array(
            '__tableName_' => 'user_settings',
            'user_id' => 167,
            'locale' => 'pt_BR',
            'setting_name' => 'orcid',
            'setting_value' => 'http://orcid.org/0070-0081-0602-0903',
            'setting_type' => 'string',
        ),
        array(
            '__tableName_' => 'user_settings',
            'user_id' => 167,
            'locale' => 'pt_BR',
            'setting_name' => 'affiliation',
            'setting_value' => 'Avengers',
            'setting_type' => 'string',
        ),
    ),
    'interests' => array(
        array(
            '__tableName_' => 'user_interests',
            'user_id' => 7823,
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
            'user_id' => 7823,
            'controlled_vocab_entry_id' => 14716,
            'controlled_vocab_entries' => array(
                array(
                    '__tableName_' => 'controlled_vocab_entries',
                    'controlled_vocab_entry_id' => 14716,
                    'controlled_vocab_id' => 286,
                    'seq' => 0,
                    'settings' => array(
                        array(
                            '__tableName_' => 'controlled_vocab_entry_settings',
                            'controlled_vocab_entry_id' => 14716,
                            'locale' => 'en',
                            'setting_name' => 'interest',
                            'setting_value' => 'tranquility',
                            'setting_type' => 'string',
                        ),
                    ),
                    'controlled_vocabs' => array(
                        array(
                            '__tableName_' => 'controlled_vocabs',
                            'controlled_vocab_id' => 286,
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
