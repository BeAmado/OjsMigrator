<?php

return array(
    'select' => array(
        'users' => array(
            'query' => ''
              . 'SELECT DISTINCT u.* '
              . 'FROM roles r '
              . 'INNER JOIN users u '
              .     'ON u.user_id = r.user_id '
              . 'WHERE r.journal_id = :selectUsers_journalId',

            'params' => array(
                'journal_id' => ':selectUsers_journalId'
            ),
        ),
        'user_settings' => array(
            'query' => ''
              . 'SELECT us.* '
              . 'FROM user_settings us '
              . 'WHERE us.user_id = :selectUserSettings_userId',

            'params' => array(
                'user_id' => ':selectUserSettings_userId',
            ),
        ),
        'roles' => array(
            'query' => ''
              . 'SELECT r.* '
              . 'FROM roles r '
              . 'WHERE '
              .     'r.user_id = :selectUserRoles_userId AND '
              .     'r.journal_id = :selectUserRoles_journalId',

            'params' => array(
                'user_id'    => ':selectUserRoles_userId',
                'journal_id' => ':selectUserRoles_journalId',
            ),
        ),
        'user_interests' => array(
            'query' => ''
              . 'SELECT '
              .     'ui.user_id, '
              .     'ui.controlled_vocab_entry_id, '
              .     'cves.locale, '
              .     'cves.setting_value AS interest, '
              .     'cve.seq, '
              .     'cve.controlled_vocab_entry, '
              .     'cv.controlled_vocab_id, '
              .     'cv.symbolic, '
              .     'cv.assoc_type, '
              .     'cv.assoc_id '
              . 'FROM user_interests ui '
              . 'INNER JOIN controlled_vocab_entry_settings cves '
              .     'ON cves.controlled_vocab_entry_id = ui.controlled_vocab_entry_id '
              . 'INNER JOIN controlled_vocab_entries cve '
              .     'ON cve.controlled_vocab_entry_id = ui.controlled_vocab_entry_id '
              . 'INNER JOIN controlled_vocabs cv '
              .     'ON cv.controlled_vocab_id = cve.controlled_vocab_id '
              . 'WHERE ui.user_id = :selectUserInterests_userId',

            'params' => array(
                'user_id' => ':selectUserInterests_userId',
            ),
        ),
    ),
    'insert' => array(
        'user' => array(),
        'user_setting' => array(),
        'user_interest' => array(),
        'controlled_vocab' => array(),
        'controlled_vocab_entry' => array(),
        'controlled_vocab_entry_settings' => array(),
    ),
    'update' => array(
        'user' => array(),
        'user_setting' => array(),
        'user_interest' => array(),
        'controlled_vocab' => array(),
        'controlled_vocab_entry' => array(),
        'controlled_vocab_entry_settings' => array(),
    ),
);
