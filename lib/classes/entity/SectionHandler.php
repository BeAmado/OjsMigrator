<?php

namespace BeAmado\OjsMigrator\Entity;
use \BeAmado\OjsMigrator\Registry;

class SectionHandler extends EntityHandler
{
    public function create($data, $extra = null)
    {
        return new Entity($data, 'sections');
    }

    protected function registerSection($data)
    {
        /*$section = $this->getValidData('sections', $data);

        if (!Registry::get('DataMapper')->isMapped(
            'journals',
            $section->getData('journal_id')
        ))
            return false;
            // TODO: TREAT BETTER

        $section->set(
            'journal_id',
            Registry::get('DataMapper')->getMapping(
                'journals',
                $section->getData('journal_id')
            )
        );

        if (Registry::get('DataMapper')->isMapped(
            'review_forms',
            $section->getData('review_form_id')
        ))
            $section->set(
                'review_form_id',
                Registry::get('DataMapper')->getMapping(
                    'review_forms',
                    $section->getData('review_form_id')
                )
            );
        
        return $this->createInDatabase($section);*/
        return $this->importEntity(
            $data,
            'sections',
            array(
                'journals' => 'journal_id',
                'review_forms' => 'review_form_id',
            )
            true
        );
    }

    protected function importSectionSetting($data)
    {
        /*$setting = $this->getValidData('section_settings', $data);

        $setting->set(
            'section_id',
            Registry::get('DataMapper')->getMapping(
                'sections',
                $setting->getData('section_id')
            )
        );
        
        return $this->createOrUpdateInDatabase($setting);*/
        return $this->importEntity(
            $data,
            'review_form_settings',
            array('sections' => 'section_id'),
            true
        );
    }

    protected function importSectionEditor($data)
    {
        /*$sectionEditor = $this->getValidData('section_editors', $data);

        if (!Registry::get('DataMapper')->isMapped(
            'users',
            $sectionEditor->getData('user_id')
        ))
            return false;
            // TODO: TREAT BETTER

        if (!Registry::get('DataMapper')->isMapped(
            'sections',
            $sectionEditor->getData('section_id')
        ))
            return false;
            // TODO: TREAT BETTER

        if (!Registry::get('DataMapper')->isMapped(
            'journals',
            $sectionEditor->getData('journal_id')
        ))
            return false;
            // TODO: TREAT BETTER

        $this->setMappedData($sectionEditor, array(
            'users' => 'user_id',
            'sections' => 'section_id',
            'journals' => 'journal_id',
        ));

        return $this->createorUpdateInDatabase($sectionEditor);*/
        return $this->importEntity(
            $data,
            'section_editors',
            array(
                'users' => 'user_id',
                'sections' => 'section_id',
                'journals' => 'journal_id',
            )
        );
    }

    public function importSection($section)
    {
        try {
            if (!\is_a($section, \BeAmado\OjsMigrator\Entity\Entity::class))
                $section = $this->create($section);

            if ($section->getTableName() != 'sections')
                return false;

            if (
                !Registry::get('DataMapper')->isMapped(
                    'sections',
                    $section->getId()
                ) &&
                !$this->registerSection($section)
            )
                return false;

            // import the settings
            $section->get('settings')->forEachValue(function($setting) {
                $this->importSectionSetting($setting);
            });

            // import the section editors
            $section->get('section_editors')->forEachValue(function($se) {
                $this->importSectionEditor($se);
            });
        } catch (\Exception $e) {
            // TODO: TREAT THE Exception
            echo \PHP_EOL . \PHP_EOL . $e->getMessage() . \PHP_EOL . \PHP_EOL;
            return false;
        }

        return true;
    }

    protected function getSectionSettings($section)
    {
        if (!$section->hasAttribute('section_id'))
            return;

        return Registry::get('SectionSettingsDAO')->read(array(
            'section_id' => $section->get('section_id')->getValue(),
        ));
    }

    protected function getSectionEditors($section)
    {
        if (!$section->hasAttribute('section_id'))
            return;

        return Registry::get('SectionEditorsDAO')->read(array(
            'section_id' => $section->get('section_id')->getValue(),
        ));
    }

    public function exportSectionsFromJournal($journal)
    {
        Registry::get('SectionsDAO')->dumpToJson(array(
            'journal_id' => \is_numeric($journal)
                ? (int) $journal
                : $journal->get('journal_id')->getValue()
        ));

        foreach (Registry::get('FileSystemManager')->listdir(
            $this->getEntityDataDir('sections')
        ) as $filename) {
            $sec = Registry::get('JsonHandler')->createFromFile($filename);

            $sec->set(
                'settings',
                $this->getSectionSettings($sec)
            );

            $sec->set(
                'editors',
                $this->getSectionEditors($sec)
            );
        }
    }
}
