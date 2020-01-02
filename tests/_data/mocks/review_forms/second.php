<?php

return array(
    '__tableName_' => 'review_forms',
    'review_form_id' => 10000,
    'assoc_type' => 256,
    'assoc_id' => '[test_journal_id]',
    'seq' => 0,
    'is_active' => 1,
    'settings' => array(
        array(
            '__tableName_' => 'review_form_settings',
            'review_form_id' => 10000,
            'locale' => 'en',
            'setting_name' => 'favorite place',
            'setting_value' => 'Home',
            'setting_type' => 'string',
        ),
    ),
    'elements' => array(
        array(
            '__tableName_' => 'review_form_elements',
            'review_form_element_id' => 1,
            'review_form_id' => 10000,
            'seq' => 5,
            'element_type' => 256,
            'required' => 0,
            'included' => 1,
            'settings' => array(
                array(
                    '__tableName_' => 'review_form_element_settings',
                    'review_form_element_id' => 1,
                    'locale' => 'en',
                    'setting_name' => 'Lead guitar',
                    'setting_value' => 'Jake E. Lee',
                    'setting_type' => 'string',
                ),
            ),
        ),
        array(
            '__tableName_' => 'review_form_elements',
            'review_form_element_id' => 21000,
            'review_form_id' => 10000,
            'seq' => 7,
            'element_type' => 256,
            'required' => 1,
            'included' => 1,
            'settings' => array(
                array(
                    '__tableName_' => 'review_form_element_settings',
                    'review_form_element_id' => 21000,
                    'locale' => 'en',
                    'setting_name' => 'drums',
                    'setting_value' => 'Dean Castronovo',
                    'setting_type' => 'string',
                ),
            ),
        ),
    ),
);
