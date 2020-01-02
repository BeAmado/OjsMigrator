<?php

namespace BeAmado\OjsMigrator\Entity;
use \BeAmado\OjsMigrator\Registry;

class ReviewFormHandler extends EntityHandler
{
    public function create($data, $extra = null)
    {
        return new Entity($data, 'review_forms');
    }

    /**
     * Inserts or updates the review_form_element_settings data.
     *
     * @param \BeAmado\OjsMigrator\MyObject $data
     * @return boolean
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

        return $this->createOrUpdateInDatabase($setting);
    }

    /**
     * Inserts or updates the review_form_elements data.
     *
     * @param \BeAmado\OjsMigrator\MyObject $data
     * @return boolean
     */
    protected function importReviewFormElement($data)
    {
        $element = $this->getValidData('review_form_elements', $data);

        $element->set(
            'review_form_id',
            Registry::get('DataMapper')->getMapping(
                'review_forms',
                $element->getData('review_form_id')
            )
        );

        $this->createOrUpdateInDatabase($element);

        if (!Registry::get('DataMapper')->isMapped(
            'review_form_elements',
            $element->getId()
        ))

        if (!$data->hasAttribute('settings'))
            return;

        $data->get('settings')->forEachValue(function($setting) {
            $this->importReviewFormElementSetting($setting);
        });

        return true;
    }

    /**
     * Inserts or updates the review_form_settings data.
     *
     * @param \BeAmado\OjsMigrator\MyObject $data
     * @return boolean
     */
    protected function importReviewFormSetting($data)
    {
        $setting = $this->getValidData('review_form_settings', $data);
        $setting->set(
            'review_form_id',
            Registry::get('DataMapper')->getMapping(
                'review_forms',
                $setting->getData('review_form_id')
            )
        );
        return $this->createOrUpdateInDatabase($setting);
    }

    /**
     * Puts the review form in the database, mapping its id.
     *
     * @param \BeAmado\OjsMigrator\MyObject $data
     * @return boolean
     */
    protected function registerReviewForm($data)
    {
        $reviewForm = $this->getValidData('review_forms', $data);
        $reviewForm->set(
            'assoc_id',
            Registry::get('DataMapper')->getMapping(
                'journals',
                $reviewForm->getData('assoc_id')
            )
        );
        return $this->createInDatabase($reviewForm);
    }

    /**
     * Imports all of the review form data in the given object.
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
        foreach ($reviewForm->getData('elements') as $element) {
            $this->importReviewFormElement($element);
        }

        return true;
    }

    protected function getReviewFormElementSettings($element)
    {
        return Registry::get('ReviewFormElementSettingsDAO')->read(array(
            'review_form_element_id' => $element->getId(),
        ));
    }

    protected function getReviewFormElements($reviewForm)
    {
        $elements = Registry::get('ReviewFormElementsDAO')->read(array(
            'review_form_id' => $reviewForm->get('review_form_id')->getValue(),
        ));

        if (!\is_a($elements, \BeAmado\OjsMigrator\MyObject::class))
            return;

        $elements->forEachValue(function ($e) {
            $e->set(
                'settings',
                $this->getReviewFormElementSettings($e)
            );
        });

        return $elements;
    }

    protected function getReviewFormSettings($reviewForm)
    {
        return Registry::get('ReviewFormSettingsDAO')->read(array(
            'review_form_id' => $reviewForm->get('review_form_id')->getValue(),
        ));
    }

    public function exportReviewFormsFromJournal($journal)
    {
        Registry::get('ReviewFormsDAO')->dumpToJson(array(
            'assoc_id' => \is_numeric($journal)
                ? (int) $journal
                : $journal->get('journal_id')->getValue()
        ));

        foreach (Registry::get('FileSystemManager')->listdir(
            $this->getEntityDataDir('review_forms')
        ) as $filename) {
            $rev = Registry::get('JsonHandler')->createFromFile($filename);
            $rev->set(
                'settings',
                $this->getReviewFormSettings($rev)
            );
            $rev->set(
                'elements',
                $this->getReviewFormElements($rev)
            );
            Registry::get('JsonHandler')->dumpToFile(
                $filename,
                $rev
            );
        }
    }
}
