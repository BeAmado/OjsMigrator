<?php

return array(
    '__tableName_' => 'sections',
    'section_id' => 3346,
    'journal_id' => '[test_journal_id]',
    'review_form_id' => '[first_review_form_id]',
    'seq' => 0,
    'editor_restricted' => 1,
    'meta_indexed' => 0,
    'meta_reviewed' => 1,
    'abstracts_not_required' => 0,
    'hide_title' => 1,
    'hide_author' => 1,
    'hide_about' => 1,
    'disable_comments' => 1,
    'abstract_word_count' => 30,
    'settings' => array(
        array(
            '__tableName_' => 'section_settings',
            'section_id' => 3346,
            'locale' => 'fr_CA',
            'setting_name' => 'title',
            'setting_value' => 'Les Sports',
            'setting_type' => 'string',
        ),
        /*
         * other section settings
         */
    ),
    'editors' => array(
        array(
            '__tableName_' => 'section_editors',
            'journal_id' => '[test_journal_id]',
            'section_id' => 3346,
            'user_id' => '[hulk_user_id]',
            'can_edit' => 1,
            'can_review' => 1,
        ),
        /*
         * other section editors
         */
    )
);
