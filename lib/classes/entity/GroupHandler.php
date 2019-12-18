<?php

namespace BeAmado\OjsMigrator\Entity;
use \BeAmado\OjsMigrator\Registry;

class GroupHandler extends EntityHandler
{
    public function create($data, $extra = null)
    {
        return new Entity($data, 'groups');
    }

    protected function registerGroup($data)
    {
        $group = $this->getValidData('groups', $data);
        $group->set(
            'assoc_id',
            Registry::get('DataMapper')->getMapping(
                'journals',
                $group->getData('assoc_id')
            )
        );

        return $this->createInDatabase($group);
    }

    protected function importGroupSetting($data)
    {
        $setting = $this->getValidData('group_settings', $data);
        $setting->set(
            'group_id',
            Registry::get('DataMapper')->getMapping(
                'groups',
                $setting->getData('group_id')
            )
        );

        return $this->createOrUpdateInDatabase($setting);
    }

    protected function importGroupMembership($data)
    {
        $membership = $this->getValidData('group_memberships', $data);
        if (!Registry::get('DataMapper')->isMapped(
            'users',
            $membership->getData('user_id')
        )) {

        }

        if (!Registry::get('DataMapper')->isMapped(
            'users', 
            $membership->getData('user_id')
        ))
            return false;
            // TODO: treat better

        $membership->set(
            'group_id',
            Registry::get('DataMapper')->getMapping(
                'groups',
                $membership->getData('group_id')
            )
        );

        $membership->set(
            'user_id',
            Registry::get('DataMapper')->getMapping(
                'users',
                $membership->getData('user_id')
            )
        );

        return $this->createOrUpdateInDatabase($membership);
    }

    public function importGroup($data)
    {
        $group = $this->create($data);
        if (
            !Registry::get('DataMapper')->isMapped(
                'groups',
                $group->getId()
            ) &&
            !$this->registerGroup($group)
        )
            return false;
            // TODO: treat better

        foreach ($group->getData('settings') as $setting) {
            $this->importGroupSetting($setting);
        }

        foreach ($group->getData('memberships') as $membership) {
            $this->importGroupMembership($membership);
        }

        return true;
    }
}
