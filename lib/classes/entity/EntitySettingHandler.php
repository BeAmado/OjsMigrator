<?php

namespace BeAmado\OjsMigrator\Entity;

class EntitySettingHandler extends EntityHandler
{
    protected function hasTableNameAttribute($setting)
    {
        return $this->isMyObject($setting) &&
            $setting->hasAttribute('__tableName_');
    }

    protected function tableNameIsSettings($tableName)
    {
        if (!\is_string($tableName))
            return false;

        return \in_array('settings', \explode('_', $tableName));
    }

    public function isSetting($setting)
    {
        return $this->tableNameIsSettings($this->entityTableName($setting)) &&
            $setting->hasAttribute('setting_name') &&
            $setting->hasAttribute('setting_value') &&
            $setting->hasAttribute('setting_type');
    }

    public function getSettingType($setting)
    {
        if ($this->isSetting($setting))
            return $setting->get('setting_type')->getValue();
    }

    public function getSettingValue($setting)
    {
        if ($this->isSetting($setting))
            return $setting->get('setting_value')->getValue();
    }

    public function settingHasTypeObject($setting)
    {
        return \strtolower($this->getSettingType($setting)) === 'object';
    }
}
