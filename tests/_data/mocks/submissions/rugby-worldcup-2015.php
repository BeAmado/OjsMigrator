<?php

return array(
    '__tableName_' => '[submissions_table]',
    '[submission_id]' => 7384,
    'locale' => 'en_NZ',
    'user_id' => '[ironman_user_id]',
    'journal_id' => '[test_journal_id]',
    'section_id' => '[sports_section_id]',
    'language' => 'en',
    'comments_to_ed' => null,
    'citations' => null,
    'date_submitted' => '2015-12-09 13:15:26',
    'last_modified' => '2015-12-20 23:14:57',
    'date_status_modified' => '2015-12-09 19:09:34',
    'status' => 3,
    'submission_progress' => 0,
    'current_round' => 1,
    'submission_file_id' => 336,
    'revised_file_id' => 337,
    'review_file_id' => null,
    'editor_file_id' => null,
    'pages' => '27-40',
    'fast_tracked' => 0,
    'hide_author' => 0,
    'comments_status' => 0,

    // associated entities
    'published' => array(
        '__tableName_' => '[published_submissions_table]',
        '[published_submission_id]' => 77,
        '[submission_id]' => 7384,
        'issue_id' => '[rwc2015_issue_id]',
        'date_published' => '2015-12-30 12:00:23',
        'seq' => 1,
        'access_status' => 0,
    ),
    'settings' => array(
        array(
            '__tableName_' => '[submission_settings_table]',
            '[submission_id]' => 7384,
            'locale' => 'en_NZ',
            'setting_name' => 'title',
            'setting_value' => 'The Rugby World Cup 2015',
            'setting_type' => 'string',
        ),
        /*
         * other settings
         */
    ),
    'files' => array(
        // submission_file
        array(
            '__tableName_' => '[submission_files_table]',
            'file_id' => 336,
            'revision' => 1,
            'source_file_id' => null,
            'source_revision' => null,
            '[submission_id]' => 7384,
            'file_name' => '7384-336-1-SM.doc',
            'file_type' => 'application/msword',
            'file_size' => 123412,
            'original_file_name' => 'rwc2015-pre',
            'file_stage' => 1,
            'viewable' => null,
            'date_uploaded' => '2015-10-12 12:34:56',
            'date_modified' => '2015-10-12 12:34:56',
            'round' => 1,
            'assoc_id' => null,
        ),
        // revised_file
        array(
            '__tableName_' => '[submission_files_table]',
            'file_id' => 337,
            'revision' => 1,
            'source_file_id' => 336,
            'source_revision' => 1,
            '[submission_id]' => 7384,
            'file_name' => '7384-337-1-RV.docx',
            'file_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'file_size' => 123980,
            'original_file_name' => 'rwc2015-rev',
            'file_stage' => 2,
            'viewable' => null,
            'date_uploaded' => '2015-10-12 12:34:56',
            'date_modified' => '2015-10-12 12:34:56',
            'round' => 1,
            'assoc_id' => null,
        ),
        array(
            '__tableName_' => '[submission_files_table]',
            'file_id' => 338,
            'revision' => 1,
            'source_file_id' => 337,
            'source_revision' => 1,
            '[submission_id]' => 7384,
            'file_name' => '7384-338-1-PB.pdf',
            'file_type' => 'application/pdf',
            'file_size' => 123139,
            'original_file_name' => 'rwc2015-final',
            'file_stage' => 7,
            'viewable' => null,
            'date_uploaded' => '2015-10-12 12:34:56',
            'date_modified' => '2015-10-12 12:34:56',
            'round' => 1,
            'assoc_id' => null,
        ),
        array(
            '__tableName_' => '[submission_files_table]',
            'file_id' => 338,
            'revision' => 1,
            'source_file_id' => 336,
            'source_revision' => 1,
            '[submission_id]' => 7384,
            'file_name' => '7384-338-1-SP.pdf',
            'file_type' => 'application/pdf',
            'file_size' => 123980,
            'original_file_name' => 'rwc2015-supp',
            'file_stage' => 6,
            'viewable' => null,
            'date_uploaded' => '2015-10-12 12:34:56',
            'date_modified' => '2015-10-12 12:34:56',
            'round' => 1,
            'assoc_id' => null,
        ),
    ),
    'supplementary_files' => array(
        array(
            '__tableName_' => '[supplementary_files_table]',
            'supp_id' => 2187,
            'file_id' => 338,
            '[submission_id]' => 7384,
            'type' => null,
            'language' => null,
            'date_created' => '2015-12-22',
            'show_reviewers' => 0,
            'date_submitted' => '2015-12-22 12:09:38',
            'seq' => 0,
            'remote_url' => null,
            'settings' => array(
                array(
                    '__tableName_' => '[supp_file_settings_table]',
                    'supp_id' => 2187,
                    'locale' => 'en_NZ',
                    'setting_name' => 'subject',
                    'setting_value' => 'Rugby',
                    'setting_type' => 'string',
                ),
                array(
                    '__tableName_' => '[supp_file_settings_table]',
                    'supp_id' => 2187,
                    'locale' => 'en_NZ',
                    'setting_name' => 'title',
                    'setting_value' => 'New comers',
                    'setting_type' => 'string',
                ),
            ),
        ),
    ),
    'galleys' => array(
        array(
            '__tableName_' => '[galleys_table]',
            'galley_id' => 22,
            'locale' => 'en_NZ',
            '[submission_id]' => 7384,
            'file_id' => 338,
            'label' => 'PDF',
            'html_galley' => 0,
            'style_file_id' => null,
            'seq' => 0,
            'remote_url' => null,
            'settings' => array(
                array(
                    '__tableName_' => '[galley_settings_table]',
                    'galley_id' => 22,
                    'locale' => 'en_NZ',
                    'setting_name' => 'excludeDoi',
                    'setting_value' => 0,
                    'setting_type' => 'int',
                ),
            ),
        ),
    ),
    'comments' => array(
        array(
            '__tableName_' => '[comments_table]',
            'comment_id' => 33,
            'comment_type' => 2,
            'role_id' => 256,
            '[submission_id]' => 7384,
            'assoc_id' => 7384,
            'author_id' => '[hulk_user_id]',
            'comment_title' => 'Simple comment',
            'comments' => 'Just commenting',
            'date_posted' => '2015-12-20 12:09:09',
            'date_modified' => null,
            'viewable' => 1,
        ),
        /*
         * other submission comments
         */
    ),
    'keywords' => array(
        array(
            '__tableName_' => '[search_objects_table]',
            'object_id' => 182,
            '[submission_id]' => 7384,
            'type' => 16,
            'assoc_id' => 7483,
            'search_object_keywords' => array(
                array(
                    '__tableName_' => '[search_object_keywords_table]',
                    'object_id' => 182,
                    'pos' => 231,
                    'keyword_id' => 89231,
                    'keyword_list' => array(
                        '__tableName_' => '[search_keyword_list_table]',
                        'keyword_id' => 89231,
                        'keyword_text' => 'best',
                    )
                ),
            )
        ),
        /*
         * other keywords
         */
    ),
    'authors' => array(
        array(
            '__tableName_' => 'authors',
            'author_id' => 8877,
            /*
             * remaining author fields
             */
            'settings' => array(),
        ),
        /*
         * other authors
         */
    ),
    'edit_assignments' => array(
        array(
            '__tableName_' => 'edit_assignments',
            'edit_id' => 1000,
            '[submission_id]' => 7384,
            'editor_id' => '[greenlantern_user_id]',
            /*
             * remaining edit_assigments fields
             */
        ),
        /*
         * other edit assignments
         */
    ),
    'edit_decisions' => array(
        array(),
        /*
         * other edit decisions
         */
    ),
    'history' => array(
        'event_logs' => array(
            array(
                '__tableName_' => 'event_log',
                'log_id' => 998,
                /*
                 * remaining event_log fields
                 */
                'settings' => array(
                    array(
                        '__tableName_' => 'event_log_settings',
                        'log_id' => 998,
                        /*
                         * remaining event_log_settings fields
                         */
                    )
                ),
            ),
            /*
             * other event logs
             */
        ),
        'email_logs' => array(
            array(
                '__tableName_' => 'email_log',
                'log_id' => 2390,
                /*
                 * remaining email_log fields
                 */
                'users' => array(
                    array(
                        'email_log_id' => 2390,
                        'user_id' => '[thor_user_id]',
                    ),
                    /*
                     * other email log users
                     */
                )
            ),
            /*
             * other email logs
             */
        ),
    ),
    'review_assignments' => array(
        array(),
        /*
         * other review assignments
         */
    ),
    'review_rounds' => array(
        array(),
        /*
         * other review rounds
         */
    ),
);
