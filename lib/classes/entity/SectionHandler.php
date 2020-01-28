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
        return $this->importEntity(
            $data,
            'sections',
            array(
                'journals' => 'journal_id',
                'review_forms' => 'review_form_id',
            ),
            true
        );
    }

    protected function importSectionSetting($data)
    {
        return $this->importEntity(
            $data,
            'section_settings',
            array('sections' => 'section_id')
        );
    }

    protected function importSectionEditor($data)
    {
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
            if ($section->hasAttribute('settings'))
                $section->get('settings')->forEachValue(function($setting) {
                    $this->importSectionSetting($setting);
                });

            // import the section editors
            if ($section->hasAttribute('editors'))
                $section->get('editors')->forEachValue(function($se) {
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
