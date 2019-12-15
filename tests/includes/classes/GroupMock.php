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
            \substr($group->get('assoc_id')->getValue(), 1, -1), // remove the []
            '_id',
            ''
        );

        $journal = (new JournalMock())->get($path);

        $group->set(
            'assoc_id',
            $journal->get('journal_id')->getValue()
        );

        return $group;
    }

    protected function fillUserId($membership)
    {
        $username = \str_replace(
            \str_replace(
                $membership->get('user_id')->getValue(),
                '_id',
                ''
            ),
            '_user',
            ''
        );

        var_dump($username);

        $user = (new UserMock())->get($username);

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
