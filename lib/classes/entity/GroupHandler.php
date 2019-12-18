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

    protected function getGroupMemberships($group)
    {
        if (
            !\is_numeric($group) &&
            (
                !\is_a($group, \BeAmado\OjsMigrator\MyObject::class) ||
                !$group->hasAttribute('group_id') ||
                $group->get('group_id')->getValue() == null
            )
        )
            return;

        return Registry::get('GroupMembershipsDAO')->read(array(
            'group_id' => \is_numeric($group)
                ? $group
                : $group->get('group_id')->getValue()
        ));
    }

    protected function getGroupSettings($group)
    {
        if (
            !\is_numeric($group) &&
            (
                !\is_a($group, \BeAmado\OjsMigrator\MyObject::class) ||
                !$group->hasAttribute('group_id') ||
                $group->get('group_id')->getValue() == null
            )
        )
            return;

        return Registry::get('GroupSettingsDAO')->read(array(
            'group_id' => \is_numeric($group)
                ? $group
                : $group->get('group_id')->getValue()
        ));
    }

    public function exportGroupsFromJournal($journal)
    {
        if (
            !\is_numeric($journal) &&
            (
                !$this->isEntity($journal) ||
                $journal->getId() == null
            )
        )
            return;
        
        Registry::get('GroupsDAO')->dumpToJson(array(
            'assoc_id' => \is_numeric($journal) 
                ? (int) $journal 
                : $journal->getId(),
        ));

        foreach (Registry::get('FileSystemManager')->listdir(
            $this->getEntityDataDir('groups')
        ) as $filename) {
            $group = $this->create(
                Registry::get('JsonHandler')->createFromFile($filename)
            );

            $group->set(
                'settings',
                $this->getGroupSettings()
            );

            $group->set(
                'memberships',
                $this->getGroupMemberships()
            );

            Registry::get('JsonHandler')->dumpToFile(
                $filename,
                $group
            );
        }
    }
}
