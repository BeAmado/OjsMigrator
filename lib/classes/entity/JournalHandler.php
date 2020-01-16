<?php

namespace BeAmado\OjsMigrator\Entity;

class JournalHandler extends EntityHandler
{
    public function create($data, $extra = null)
    {
        return new Entity($data, 'journals');
    }

    protected function importJournalSetting($data)
    {
        $setting = $this->getValidData('journal_settings', $data);
        if (!Registry::get('DataMapper')->isMapped(
            'journals', 
            $setting->getData('journal_id')
        ))
            return false;

        $this->setMappedData($setting, array(
            'journals' => 'journal_id',
        ));

        return $this->createOrUpdateInDatabase($setting);
    }

    protected function importPluginSetting($data)
    {
        $plgSetting = $this->getValidData('plugin_settings', $data);
        if (!Registry::get('DataMapper')->isMapped(
            'journals',
            $plgSetting->getData('journal_id')
        ))
            return false;

        $this->setMappedData($plgSetting, array(
            'journals' => 'journal_id',
        ));

        return $this->createOrUpdateInDatabase($plgSetting);
    }

    public function importJournal($journal)
    {
        try {
            if (!Registry::get('DataMapper')->isMapped(
                'journals',
                $journal->get('journal_id')->getValue()
            ))
                return false;

            if ($journal->hasAttribute('settings'))
                $journal->get('settings')->forEachValue(function($setting) {
                    $this->importJournalSetting($setting);
                });

            if ($journal->hasAttribute('plugins'))
                $journal->get('plugins')->forEachValue(function($plugin) {
                    $this->importPluginSetting($plugin);
                });
        } catch (\Exception $e) {
            // TODO: TREAT BETTER
            echo \PHP_EOL . \PHP_EOL . $e->getMessage() . \PHP_EOL . \PHP_EOL;
            return false;
        }

        return true;
    }

    protected function getJournalSettings($journal)
    {

    }
}
