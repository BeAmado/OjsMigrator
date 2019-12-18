<?php

namespace BeAmado\OjsMigrator;

class GroupMock extends EntityMock
{
    public function __construct($name = null)
    {
        parent::__construct('groups');
    }

    protected function fillJournalId($group)
    {
        $path = \str_replace(
            '_id',
            '',
            $this->removeBrackets($group->get('assoc_id')->getValue()) // remove the []
        );
        
        $journal = (new JournalMock())->getJournal($path);

        $group->set(
            'assoc_id',
            $journal->get('journal_id')->getValue()
        );

        return $group;
    }

    protected function fillUserId($membership)
    {
        $username = \str_replace(
            '_user',
            '',
            \str_replace(
                '_id',
                '',
                $this->removeBrackets($membership->get('user_id')->getValue())
            )
        );

        $user = (new UserMock())->getUser($username);

        $membership->set(
            'user_id',
            $user->get('user_id')->getValue()
        );

        //return $membership;
    }

    protected function fill($group)
    {
        $group->get('memberships')->forEachValue(function($m) {
            $this->fillUserId($m);
        });

        return $this->fillJournalId($group);
    }

    public function getGroupForwards()
    {
        return $this->fill($this->get('forwards'));
    }

    public function getGroupBacks()
    {
        return $this->fill($this->get('backs'));
    }
}
