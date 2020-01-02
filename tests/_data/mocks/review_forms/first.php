<?php

return array(
    '__tableName_' => 'review_forms',
    'review_form_id' => 6120,
    'assoc_type' => 256,
    'assoc_id' => '[test_journal_id]',
    'seq' => 0,
    'is_active' => 1,
    'settings' => array(
        array(
            '__tableName_' => 'review_form_settings',
            'review_form_id' => 6120,
            'locale' => 'en',
            'setting_name' => 'location',
            'setting_value' => 'Very far away',
            'setting_type' => 'string',
        ),
        array(
            '__tableName_' => 'review_form_settings',
            'review_form_id' => 6120,
            'locale' => 'es',
            'setting_name' => 'ubicacion',
            'setting_value' => 'Muy distante',
            'setting_type' => 'string',
        ),
    ),
    'elements' => array(
        array(
            '__tableName_' => 'review_form_elements',
            'review_form_element_id' => 28,
            'review_form_id' => 6120,
            'seq' => 1,
            'element_type' => 256,
            'required' => 0,
            'included' => 1,
            'settings' => array(
                array(
                    '__tableName_' => 'review_form_element_settings',
                    'review_form_element_id' => 28,
                    'locale' => 'en',
                    'setting_name' => 'Lead guitar',
                    'setting_value' => 'Joe Perry',
                    'setting_type' => 'string',
                ),
            ),
        ),
        array(
            '__tableName_' => 'review_form_elements',
            'review_form_element_id' => 8200,
            'review_form_id' => 6120,
            'seq' => 2,
            'element_type' => 256,
            'required' => 0,
            'included' => 1,
            'settings' => array(
                array(
                    '__tableName_' => 'review_form_element_settings',
                    'review_form_element_id' => 8200,
                    'locale' => 'en',
                    'setting_name' => 'drums',
                    'setting_value' => 'Matt Sorum',
                    'setting_type' => 'string',
                ),
            ),
        ),
    ),
);
