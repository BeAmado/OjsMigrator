<?php

return array(
    '__tableName_' => 'sections',
    'section_id' => 9900,
    'journal_id' => '[test_journal_id]',
    'review_form_id' => '[second_review_form_id]',
    'seq' => 1,
    'editor_restricted' => 0,
    'meta_indexed' => 0,
    'meta_reviewed' => 1,
    'abstracts_not_required' => 0,
    'hide_title' => 0,
    'hide_author' => 0,
    'hide_about' => 0,
    'disable_comments' => 1,
    'abstract_word_count' => 30,
    'settings' => array(
        array(
            '__tableName_' => 'section_settings',
            'section_id' => 9900,
            'locale' => 'fr_CA',
            'setting_name' => 'title',
            'setting_value' => 'Sciences',
            'setting_type' => 'string',
        ),
        array(
            '__tableName_' => 'section_settings',
            'section_id' => 9900,
            'locale' => 'fr_CA',
            'setting_name' => 'abbrev',
            'setting_value' => 'SCI',
            'setting_type' => 'string',
        ),
    ),
    'editors' => array(
        array(
            '__tableName_' => 'section_editors',
            'journal_id' => '[test_journal_id]',
            'section_id' => 9900,
            'user_id' => '[ironman_user_id]',
            'can_edit' => 1,
            'can_review' => 0,
        ),
        array(
            '__tableName_' => 'section_editors',
            'journal_id' => '[test_journal_id]',
            'section_id' => 9900,
            'user_id' => '[batman_user_id]',
            'can_edit' => 1,
            'can_review' => 1,
        ),
        array(
            '__tableName_' => 'section_editors',
            'journal_id' => '[test_journal_id]',
            'section_id' => 9900,
            'user_id' => '[hulk_user_id]',
            'can_edit' => 0,
            'can_review' => 0,
        ),
    )
);
