<?php

namespace BeAmado\OjsMigrator\Entity;
use BeAmado\OjsMigrator\Registry;

class EntitySettingHandler
{
    protected function hasTableNameAttribute($setting)
    {
        return \is_a($setting, \BeAmado\OjsMigrator\MyObject::class) &&
            $setting->hasAttribute('__tableName_');
    }

    protected function tableNameIsSettings($tableName)
    {
        if (!\is_string($tableName))
            return false;

        return \in_array('settings', \explode('_', $tableName));
    }

    protected function getTableName($setting)
    {
        if ($this->hasTableNameAttribute($setting))
            return $setting->get('__tableName_')->getValue();
    }

    public function isSetting($setting)
    {
        return $this->tableNameIsSettings($this->getTableName($setting)) &&
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

    public function objectSettingOk($setting)
    {
        if (!$this->settingHasTypeObject($setting))
            return;

        return Registry::get('SerialDataHandler')->serializationIsOk(
            $this->getSettingValue($setting)
        );
    }

    protected function setSettingValue($setting, $value)
    {
        $setting->set(
            'setting_value',
            $value
        );
    }

    /**
     *
     *
     * @return void
     */
    public function fixObjectSettingValue(
        $setting,
        $skipValidations = false
    ) {
        if (
            !$skipValidations && (
                !$this->isSetting($setting) ||
                !$this->settingHasTypeObject($setting) ||
                $this->objectSettingOk($setting)
            )
        )
            return;

        $this->setSettingValue(
            $setting,
            Registry::get('SerialDataHandler')->fixSerializedData(
                $this->getSettingValue($setting)
            )
        );
    }
}
