<?php

namespace BeAmado\OjsMigrator\Entity;
use \BeAmado\OjsMigrator\Registry;
use \BeAmado\OjsMigrator\ImportExport;

class AnnouncementHandler extends EntityHandler implements ImportExport
{
    public function create($data, $extra = null)
    {
        return new Entity($data, 'announcements');
    }

    protected function registerAnnouncement($data)
    {
        return $this->importEntity(
            $data,
            'announcements',
            array('journals' => 'assoc_id'),
            true // force create in the database
        );
    }

    protected function importAnnouncementSetting($data)
    {
        return $this->importEntity(
            $data, 
            'announcement_settings', 
            array('announcements' => 'announcement_id')
        );
    }

    public function importAnnouncement($ann)
    {
        try {
            if (!\is_a($ann, \BeAmado\OjsMigrator\Entity\Entity::class))
                $ann = $this->create($ann);

            if ($ann->getTableName() !== 'announcements')
                return false;

            if (
                !Registry::get('DataMapper')->isMapped(
                    'announcements', 
                    $ann->getId()
                ) &&
                !$this->registerAnnouncement($ann)
            )
                return false;

            // import the settings
            if ($ann->hasAttribute('settings'))
                $ann->get('settings')->forEachValue(function($setting) {
                    $this->importAnnouncementSetting($setting);
                });
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
                ? (int) $ann
                : $ann->get('announcement_id')->getValue()
        ));
    }

    public function exportAnnouncementsFromJournal($journal)
    {
        if (
            !\is_numeric($journal) &&
            (
                !$this->isEntity($journal) ||
                $journal->getId() == null
            )
        )
            return;

        Registry::get('AnnouncementsDAO')->dumpToJson(array(
            'assoc_id' => \is_numeric($journal) ? $journal : $journal->getId(),
        ));

        foreach (Registry::get('FileSystemManager')->listdir(
            $this->getEntityDataDir('announcements')
        ) as $filename) {
            $announcement = $this->create(
                Registry::get('JsonHandler')->createFromFile($filename)
            );

            $announcement->set(
                'settings',
                $this->getAnnouncementSettings($announcement)
            );

            Registry::get('JsonHandler')->dumpToFile(
                $filename,
                $announcement
            );
        }
    }

    public function import($announcement)
    {
        return $this->importAnnouncement($announcement);
    }

    public function export($journal)
    {
        return $this->exportAnnouncementsFromJournal($journal);
    }
}
