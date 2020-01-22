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
        return $this->importEntity(
            $data,
            'groups',
            array('journals' => 'assoc_id'),
            true
        );
    }

    protected function importGroupSetting($data)
    {
        return $this->importEntity(
            $data,
            'group_settings',
            array('groups' => 'group_id')
        );
    }

    protected function importGroupMembership($data)
    {
        return $this->importEntity(
            $data, 
            'group_memberships', 
            array(
                'groups' => 'group_id',
                'users' => 'user_id',
            )
        );
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

        // import the settings
        if ($group->hasAttribute('settings'))
            $group->get('settings')->forEachValue(function($setting) {
                $this->importGroupSetting($setting);
            });

        // import the memberships
        if ($group->hasAttribute('memberships'))
            $group->get('memberships')->forEachValue(function($membership) {
                $this->importGroupMembership($membership);
            });

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
                $this->getGroupSettings($group)
            );

            $group->set(
                'memberships',
                $this->getGroupMemberships($group)
            );

            Registry::get('JsonHandler')->dumpToFile(
                $filename,
                $group
            );
        }
    }
}
