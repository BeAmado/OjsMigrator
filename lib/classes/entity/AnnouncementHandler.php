<?php

namespace BeAmado\OjsMigrator\Entity;
use \BeAmado\OjsMigrator\Registry;

class AnnouncementHandler extends EntityHandler
{
    public function create($data, $extra = null)
    {
        return new Entity($data, 'announcements');
    }

    protected function registerAnnouncement($data)
    {
        $ann = $this->getValidData('announcements', $data);
        return $this->createInDatabase($ann);
    }

    protected function importAnnouncementSetting($data)
    {
        $setting = $this->getValidData('announcement_settings', $data);
        $setting->set(
            'announcement_id',
            Registry::get('DataMapper')->getMapping(
                'annoucements',
                $setting->get('announcement_id')->getValue()
            )
        );
    }

    public function importAnnouncement($ann)
    {
        try {
            if (!\is_a($ann, \BeAmado\OjsMigrator\Entity\Entity::class))
                $ann = $this->create($ann);

            if ($ann->getTableName() !== 'announcements')
                return false;

            if (!Registry::get('DataMapper')->isMapped(
                'announcements', 
                $ann->getId()
            ))
                $this->registerAnnouncement($ann);

            // import the settings
            foreach ($ann->getData('settings') as $setting) {
                $this->importAnnouncementSetting($setting)
            }
        } catch (\Exception $e) {
            // TODO: treat the exception
            echo "\n\n" . $e->getMessage() . "\n\n";
            return false;
        }

        return true;
    }

    public function getAnnouncementSettings($ann)
    {
        if (
            !\is_numeric($ann) &&
            (
                !\is_a($ann, \BeAmado\OjsMigrator\MyObject::class) ||
                !$ann->hasAttribute('announcement_id') ||
                $ann->get('announcement_id')->getValue() == null
            )
        )
            return;

        return Registry::get('AnnouncementSettingsDAO')->read(array(
            'announcement_id' => \is_numeric($ann)
                ? (int) $ann,
                : $ann->get('announcement_id')->getValue()
        ));
    }

    public function exportAnnouncementsFromJournal($journal)
    {
        if (
            !\is_numeric($journal) &&
            (
                !\is_a($journal, Entity::class) ||
                !$journal->getId() == null
            )
        )
            return;

        Registry::get('StatementHandler')->execute(
            'selectAnnouncements',
            array(
                'assoc_id' => \is_numeric($journal)
                    ? (int) $journal
                    : $journal->getId()
            ),
            function($res) {
                $ann = Registry::get('EntityHandler')->getValidData(
                    'announcements',
                    $res
                );

                $ann->set(
                    'settings',
                    $this->getAnnouncementSettings($ann)
                );

                return $this->dumpEntity($ann);
            }
        );
    }
}
