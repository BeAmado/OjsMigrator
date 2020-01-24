<?php

return array(
    '__tableName_' => '[submission_table_name]',
    '[submission_id]' => 7384,
    'locale' => 'en_NZ',
    'user_id' => '[ironman_user_id]',
    'journal_id' => '[test_journal_id]',
    'section_id' => '[sports_section_id]',
    'language' => 'en',
    'comments_to_ed' => '',
    'citations' => null,
    'date_submitted' => '2015-12-09 13:15:26',
    'last_modified' => '2015-12-20 23:14:57',
    'date_status_modified' => '2015-12-09 19:09:34',
    /* 
     * remaining submission fields
     */
    'settings' => array(
        array(),
        /*
         * other settings
         */
    ),
    'files' => array(
        array(),
        /*
         * other files
         */
    ),
    'supplementary_files' => array(
        array(
            '__tableName_' => '[supplementary_files_table]',
            'supp_id' => 2187,
            /*
             * remaining submission_supplementary_files fields
             */
            'settings' => array(),
        ),
        /*
         * other supplementary files
         */
    ),
    'galleys' => array(
        array(
            '__tableName_' => '[galleys_table]',
            'galley_id' => 22,
            /*
             * remaining submission_galleys fields
             */
            'settings' => array(),
        ),
    ),
    'comments' => array(
        array(
            '__tableName_' => '[comments_table]',
            'comment_id' => 33,
            /*
             * remaining submission comments fields
             */
        ),
        /*
         * other submission comments
         */
    ),
    'html_galley_images' => array(),
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
