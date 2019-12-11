<?php

namespace BeAmado\OjsMigrator;

class GroupMock extends EntityMock
{
    public function __construct($name = null)
    {
        parent::__construct('groups');
    }

    public function getGroupForwards()
    {
        return $this->get('forwards');
    }

    public function getGroupBacks()
    {
        return $this->get('backs');
    }
}
