<?php

return array(
    '__tableName_' => 'users',
    'user_id' => 78156,
    'username' => 'greenlantern',
    'password' => 'beware green lanterns light',
    'first_name' => 'John',
    'last_name' => 'Stewart',
    'email' => 'stewart@greenlanterncorp.com',
    'roles' => array(
        array(
            '__tableName_' => 'roles',
            'role_id' => 4096,
            'journal_id' => 178,
            'user_id' => 78156,
        ),
    ),
    'settings' => array(
        array(
            '__tableName_' => 'user_settings',
            'user_id' => 78156,
            'locale' => 'pt_BR',
            'setting_name' => 'filterEditor',
            'setting_value' => '0',
            'setting_type' => 'int',
        ),
        array(
            '__tableName_' => 'user_settings',
            'user_id' => 78156,
            'locale' => 'pt_BR',
            'setting_name' => 'filterSection',
            'setting_value' => '1',
            'setting_type' => 'int',
        ),
        array(
            '__tableName_' => 'user_settings',
            'user_id' => 78156,
            'locale' => 'pt_BR',
            'setting_name' => 'affiliation',
            'setting_value' => 'Justice League',
            'setting_type' => 'string',
        ),
    ),
    'interests' => array(
        array(
            '__tableName_' => 'user_interests',
            'user_id' => 78156,
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
            'user_id' => 78156,
            'controlled_vocab_entry_id' => 18884,
            'controlled_vocab_entries' => array(
                array(
                    '__tableName_' => 'controlled_vocab_entries',
                    'controlled_vocab_entry_id' => 18884,
                    'controlled_vocab_id' => 26111,
                    'seq' => 0,
                    'settings' => array(
                        array(
                            '__tableName_' => 'controlled_vocab_entry_settings',
                            'controlled_vocab_entry_id' => 18884,
                            'locale' => 'en',
                            'setting_name' => 'interest',
                            'setting_value' => 'deep space',
                            'setting_type' => 'string',
                        ),
                    ),
                    'controlled_vocabs' => array(
                        array(
                            '__tableName_' => 'controlled_vocabs',
                            'controlled_vocab_id' => 26111,
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
            'user_id' => 78156,
            'controlled_vocab_entry_id' => 2988,
            'controlled_vocab_entries' => array(
                array(
                    '__tableName_' => 'controlled_vocab_entries',
                    'controlled_vocab_entry_id' => 2988,
                    'controlled_vocab_id' => 61781,
                    'seq' => 0,
                    'settings' => array(
                        array(
                            '__tableName_' => 'controlled_vocab_entry_settings',
                            'controlled_vocab_entry_id' => 2988,
                            'locale' => 'en',
                            'setting_name' => 'interest',
                            'setting_value' => 'Intergalatic security',
                            'setting_type' => 'string',
                        ),
                    ),
                    'controlled_vocabs' => array(
                        array(
                            '__tableName_' => 'controlled_vocabs',
                            'controlled_vocab_id' => 61781,
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
