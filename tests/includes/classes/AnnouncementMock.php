<?php

namespace BeAmado\OjsMigrator\Test;
use \BeAmado\OjsMigrator\Registry;

class AnnouncementMock extends EntityMock
{
    use JournalFiller;

    public function __construct($name = null)
    {
        parent::__construct('announcements');
    }

    public function fill($announcement)
    {
        $this->fillJournalId($announcement, 'assoc_id');

        return $announcement;
    }

    public function getAnnouncement($name)
    {
        return Registry::get('AnnouncementHandler')->create(
            $this->fill($this->get($name))
        );
    }

    public function getWelcomeAnnouncement()
    {
        return $this->getAnnouncement('welcome');
    }

    public function getInscriptionAnnouncement()
    {
        return $this->getAnnouncement('inscriptions');
    }
}
