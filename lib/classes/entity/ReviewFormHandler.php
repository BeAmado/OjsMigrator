<?php

namespace BeAmado\OjsMigrator\Entity;
use \BeAmado\OjsMigrator\Registry;
use \BeAmado\OjsMigrator\ImportExport;

class ReviewFormHandler extends EntityHandler implements ImportExport
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
        return $this->importEntity(
            $data,
            'review_form_element_settings',
            array('review_form_elements' => 'review_form_element_id')
        );
    }

    /**
     * Inserts or updates the review_form_elements data.
     *
     * @param \BeAmado\OjsMigrator\MyObject $data
     * @return void
     */
    protected function importReviewFormElement($data)
    {
        $this->importEntity(
            $data,
            'review_form_elements',
            array('review_forms' => 'review_form_id')
        );

        if ($data->hasAttribute('settings'))
            $data->get('settings')->forEachValue(function($setting) {
                $this->importReviewFormElementSetting($setting);
            });
    }

    /**
     * Inserts or updates the review_form_settings data.
     *
     * @param \BeAmado\OjsMigrator\MyObject $data
     * @return boolean
     */
    protected function importReviewFormSetting($data)
    {
        return $this->importEntity(
            $data,
            'review_form_settings',
            array('review_forms' => 'review_form_id')
        );
    }

    /**
     * Puts the review form in the database, mapping its id.
     *
     * @param \BeAmado\OjsMigrator\MyObject $data
     * @return boolean
     */
    protected function registerReviewForm($data)
    {
        return $this->importEntity(
            $data,
            'review_forms',
            array('journals' => 'assoc_id'),
            true
        );
    }

    /**
     * Imports all of the review form data in the given object.
     *
     * @param \BeAmado\OjsMigrator\Entity\Entity $reviewForm
     * @return boolean
     */
    public function importReviewForm($reviewForm)
    {
        if (!$this->isEntity($reviewForm))
            return $this->importReviewForm($this->create($reviewForm));

        if (
            !Registry::get('DataMapper')->isMapped(
                'review_forms',
                $reviewForm->getId()
            ) &&
            !$this->registerReviewForm($reviewForm)
        )
            return false;

        // importing the settings
        if ($reviewForm->hasAttribute('settings'))
            $reviewForm->get('settings')->forEachValue(function($setting) {
                $this->importReviewFormSetting($setting);
            });

        // importing the elements
        if ($reviewForm->hasAttribute('elements'))
            $reviewForm->get('elements')->forEachValue(function($element) {
                $this->importReviewFormElement($element);
            });

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

    public function import($reviewForm)
    {
        return $this->importReviewForm($reviewForm);
    }

    public function export($journal)
    {
        return $this->exportReviewFormsFromJournal($journal);
    }
}
