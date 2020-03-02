<?php

namespace BeAmado\OjsMigrator\Entity;
use \BeAmado\OjsMigrator\Registry;
use \BeAmado\OjsMigrator\ImportExport;

class JournalHandler extends EntityHandler implements ImportExport
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
        return Registry::get('JournalSettingsDAO')->read(array(
            'journal_id' => \is_numeric($journal)
                ? (int) $journal
                : $journal->get('journal_id')->getValue()
        ));
    }

    protected function getPluginSettings($journal)
    {
        return Registry::get('PluginSettingsDAO')->read(array(
            'journal_id' => \is_numeric($journal)
                ? (int) $journal
                : $journal->get('journal_id')->getValue()
        ));
    }
    
    protected function getJournalPlugins($journal)
    {
        return $this->getPluginSettings($journal);
    }

    public function exportJournal($journalId)
    {
        $res = Registry::get('JournalsDAO')->read(array(
            'journal_id' => $journalId
        ));

        if (
            !\is_a($res, \BeAmado\OjsMigrator\MyObject::class) ||
            $res->length() !== 1
        )
            return;

        $journal = $res->get(0);

        $journal->set(
            'settings', 
            $this->getJournalSettings($journal->getId())
        );

        $journal->set(
            'plugins', 
            $this->getJournalPlugins($journal->getId())
        );

        $filename = Registry::get('entitiesDir')
            . \BeAmado\OjsMigrator\DIR_SEPARATOR . 'journal.json';

        $exportedJournal = Registry::get('JsonHandler')->dumpToFile(
            $filename,
            $journal
        );

        // export the users
        // export the groups
        // export the announcements
        // export the review forms
        // export the sections
        // export the issues
        // export articles
    }

    public function import($journal)
    {
        return $this->importJournal($journal);
    }

    public function export($journal)
    {
        return $this->exportJournal($journal);
    }
}
