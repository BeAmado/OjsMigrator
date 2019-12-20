<?php

namespace BeAmado\OjsMigrator\Entity;
use \BeAmado\OjsMigrator\Registry;

class ReviewFormHandler extends EntityHandler
{
    /*
    import:
        review_form_elements
        review_form_element_settings
        review_form_responses
    */

    /**
     * 
     */
    protected function importReviewFormElementSetting($data)
    {
        $setting = $this->getValidData(
            'review_form_element_settings',
            $data
        );

        $setting->set(
            'review_form_element_id',
            Registry::get('DataMapper')->getMapping(
                'review_form_elements',
                $setting->getData('review_form_element_id')
            )
        );

        return Registry::get('ReviewFormElementSettingsDAO')
                       ->createOrUpdateInDatabase($setting);
    }

    protected function importReviewFormElement($data)
    {
        /*
        import the element
        */
        $element = $this->getValidData('review_form_elements', $data);
        $this->createOrUpdateInDatabase($element);

        if (!$data->hasAttribute('settings'))
            return;

        $data->get('settings')->forEachValue(function($setting) {
            $this->importReviewFormElementSetting($setting);
        });
    }

    protected function importReviewFormSettings()
    {

    }

    protected function registerReviewForm()
    {

    }

    /**
     *
     * @param \BeAmado\OjsMigrator\Entity\Entity $reviewForm
     * @return boolean
     */
    public function importReviewForm($reviewForm)
    {
        if (
            !Registry::get('DataMapper')->isMapped(
                'review_forms',
                $reviewForm->getId()
            ) &&
            !$this->registerReviewForm($reviewForm)
        )
            return false;

        // importing the settings
        foreach ($reviewForm->getData('settings') as $setting) {
            $this->importReviewFormSetting($setting);
        }

        // importing the elements
        /*
        foreach ($reviewForm->getData('elements') as $element) {
            $this->importReviewFormElement($element);
        }
        */

        // importing the responses

        return true;
    }

    public function exportReviewFormsFromJournal()
    {

    }
}
