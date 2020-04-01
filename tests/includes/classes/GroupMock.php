<?php

namespace BeAmado\OjsMigrator\Test;
use \BeAmado\OjsMigrator\Registry;

class GroupMock extends EntityMock
{
    use JournalFiller; // trait used to fill the journal_id (assoc_id)
    use UserFiller; // trait used to fill the user_id

    public function __construct($name = null)
    {
        parent::__construct('groups');
    }

    protected function fill($group)
    {
        $group->get('memberships')->forEachValue(function($m) {
            $this->fillUserId($m);
        });

        return $this->fillJournalId($group, 'assoc_id');
    }

    public function getGroup($name)
    {
        return Registry::get('GroupHandler')->create(
            $this->fill($this->get($name))
        );
    }

    public function getGroupForwards()
    {
//        return $this->fill($this->get('forwards'));
        return $this->getGroup('forwards');
    }

    public function getGroupBacks()
    {
//        return $this->fill($this->get('backs'));
        return $this->getGroup('backs');
    }
}
