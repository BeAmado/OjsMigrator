<?php

namespace BeAmado\OjsMigrator\Entity;
use \BeAmado\OjsMigrator\Registry;

class IssueHandler extends EntityHandler
{
    public function create($data, $extra = null)
    {
        return new Entity($data, 'issues');
    }

    protected function registerIssue($data)
    {
        $issue = $this->getValidData('issues', $data);
        $this->setMappedData($issue, array(
            'journals' => 'journal_id',
        ));
    }

    protected function importIssueSetting($data)
    {
        $setting = $this->getValidData('issue_settings', $data);
        $this->setMappedData($setting, array(
            'issues' => 'issue_id',
        ));

        return $this->createOrUpdateInDatabase($setting);
    }

    protected function importIssueFile($data)
    {
        $issueFile = $this->getValidData('issue_files', $data);
        $this->setMappedData($issueFile, array(
            'issues' => 'issue_id',
        ));

        if (!$this->createInDatabase($issueFile))
            return false;

        if (!$this->updateIssueFileName($issueFile->getData('file_name'))) {
            Registry::get('IssueFilesDAO')->delete($issueFile);
            return false;
        }

        return true;
    }

    protected function updateIssueFileName($filename)
    {
        $parts = \explode('-', $filename);
        // the file_name has the structure {issue_id}-{file_id}-PB.pdf

        $parts[0] = Registry::get('DataMapper')->getMapping(
            'issues',
            $parts[0]
        );

        $parts[1] = Registry::get('DataMapper')->getMapping(
            'issue_files',
            $parts[1]
        );

        return Registry::get('IssueFilesDAO')->update(array(
            'set' => array(
                'file_name' => \implode($parts)
            ),
            'where' => array(
                'file_id' => $parts[1]
            ),
        ));

    }

    protected function copyIssueFile($filename)
    {
        throw new \Exception('Gotta implement the copyIssueFile method from the'
            . ' class IssueHandler.');
    }

    protected function importIssueGalleySetting($data)
    {
        $setting = $this->getValidData('issue_galley_settings', $data);
        $this->setMappedData($setting, array(
            'issue_galleys' => 'galley_id',
        ));

        return $this->createInDatabase($setting);
    }

    protected function importIssueGalley($data)
    {
        $galley = $this->getValidData('issue_galleys', $data);
        $this->setMappedData($galley, array(
            'issues' => 'issue_id',
            'issue_files' => 'file_id',
        ));

        if (!$this->createInDatabase($galley))
            return false;

        if ($data->hasAttribute('settings'))
            $data->get('settings')->forEachValue(function($setting) {
                $this->importIssueGalleySetting($setting);
            });
        
        return true;
    }
    
    protected function importCustomIssueOrder($data)
    {
        $customOrder = $this->getValidData('custom_issue_orders', $data);
        $this->setMappedData($customOrder, array(
            'issues' => 'issue_id',
            'journals' => 'journal_id',
        ));

        return $this->createOrUpdateInDatabase($customOrder);
    }
    
    protected function importCustomSectionOrder($data)
    {
        $customOrder = $this->getValidData('custom_section_orders', $data);
        $this->setMappedData($customOrder, array(
            'issues' => 'issue_id',
            'sections' => 'section_id',
        ));

        return $this->createOrUpdateInDatabase($customOrder);
    }

    public function importIssue($issue)
    {
        try {
            if (!\is_a($issue, \BeAmado\OjsMigrator\Entity\Entity::class))
                $issue = $this->create($issue);

            if (
                !Registry::get('DataMapper')->isMapped(
                    'issues',
                    $issue->getId()
                ) &&
                !$this->registerIssue($issue)
            )
                return false;

            // import the issue_settings
            if ($issue->hasAttribute('settings'))
                $issue->get('settings')->forEachValue(function($setting) {
                    $this->importIssueSetting($setting);
                });

            //import the issue_files
            if ($issue->hasAttribute('files'))
                $issue->get('files')->forEachValue(function($issueFile) {
                    if (!Registry::get('DataMapper')->isMapped(
                        'issue_files',
                        $issueFile->get('file_id')->getValue()
                    ))
                        $this->importIssueFile($issueFile);
                });

            //import the issue_galleys
            if ($issue->hasAttribute('galleys'))
                $issue->get('galleys')->forEachValue(function($galley) {
                    $this->importIssueGalley($galley);
                });

            // import the custom_issue_order
            if ($issue->hasAttribute('custom_order'))
                $this->importCustomIssueOrder($issue->get('custom_order'));

            // import the custom_section_orders
            if ($issue->hasAttribute('custom_section_order'))
                $issue->get('custom_section_orders')
                      ->forEachValue(function($sectionOrder) {
                    $this->importCustomSectionOrder($sectionOrder);
                });
        } catch (\Exception $e) {
            // TODO: TREAT BETTER
            echo \PHP_EOL . \PHP_EOL . $e->getMessage() . \PHP_EOL . \PHP_EOL;
        }
    }

    protected function getIssueSettings($issue)
    {
        return Registry::get('IssueSettingsDAO')->read(array(
            'issue_id' => \is_numeric($issue)
                ? (int) $issue
                : $issue->get('issue_id')->getValue()
        ));
    }

    protected function getIssueFiles($issue)
    {
        return Registry::get('IssueFilesDAO')->read(array(
            'issue_id' => \is_numeric($issue)
                ? (int) $issue
                : $issue->get('issue_id')->getValue()
        ));
    }

    protected function getIssueGalleySettings($galley)
    {
        return Registry::get('IssueGalleySettingsDAO')->read(array(
            'galley_id' => \is_numeric($galley)
                ? (int) $galley
                : $galley->get('galley_id')->getValue()
        ));
    }

    protected function getIssueGalleys($issue)
    {
        $galleys = Registry::get('IssueGalleysDAO')->read(array(
            'issue_id' => \is_numeric($issue)
                ? (int) $issue
                : $issue->get('issue_id')->getValue()
        ));

        if (
            !\is_a($galleys, \BeAmado\OjsMigrator\MyObject::class) ||
            $galleys->length() < 1
        )
            return;

        $galleys->forEachValue(function($g) {
            $settings = $this->getIssueGalleySettings($g);

            if (
                \is_a($settings, \BeAmado\OjsMigrator\MyObject::class) &&
                $settings->length() >= 1
            )
                $g->set(
                    'settings',
                    $settings
                );
        });

        return $galleys;
    }

    protected function getCustomIssueOrder($issue)
    {
        return Registry::get('CustomIssueOrdersDAO')->read(array(
            'issue_id' => \is_numeric($issue)
                ? (int) $issue
                : $issue->get('issue_id')->getValue()
        ));
    }

    protected function getCustomSectionOrders($issue)
    {
        return Registry::get('CustomSectionOrdersDAO')->read(array(
            'issue_id' => \is_numeric($issue)
                ? (int) $issue
                : $issue->get('issue_id')->getValue()
        ));
    }

    protected function getIssueFilesDir($journal)
    {
        return Registry::get('FileSystemManager')->formPath(array(
            Registry::get('filesDir'),
            'journals',
            \is_numeric($journal)
                ? (int) $journal 
                : $journal->get('journal_id')->getValue(),
            'issues',
        ));
    }

    protected function copyIssueFiles($issue)
    {
        Registry::set(
            '__journalId__',
            $issue->get('journal_id')->getValue()
        );

        $issue->get('files')->forEachValue(function($issueFile) {
            Registry::get('FileSystemManager')->copyDir(
                Registry::get('FileSystemManager')->formPath(array(
                    $this->getIssueFilesDir(Registry::get('__journalId__')),
                    $issueFile->get('issue_id')->getValue(),
                )),
                Registry::get('FileSystemManager')->formPath(array(
                    $this->getEntityDataDir('issues'),
                    $issueFile->get('issue_id')->getValue(),
                ))
            );
        });

        Registry::remove('__journalId__');
    }

    protected function getIssueData($filename)
    {
        $issue = Registry::get('JsonHandler')->createFromFile($filename);
        $issue->set(
            'settings',
            $this->getIssueSettings($issue)
        );

        $issue->set(
            'files',
            $this->getIssueFiles($issue)
        );

        $issue->set(
            'galleys',
            $this->getIssueGalleys($issue)
        );

        $issue->set(
            'custom_order',
            $this->getCustomIssueOrder($issue)
        );

        $issue->set(
            'custom_section_orders',
            $this->getCustomSectionOrders($issue)
        );

        // copy the files
        if ($issue->get('files')->length() > 0)
            $this->copyIssueFiles($issue);

        return Registry::get('JsonHandler')->dumpToFile(
            Registry::get('FileSystemManager')->formPath(array(
                \dirname($filename),
                $issue->get('issue_id')->getValue(),
                $issue->get('issue_id')->getValue() . '.json',
            )),
            $issue
        ) && Registry::get('FileSystemManager')->removeFile($filename);
    }

    public function exportIssuesFromJournal($journal)
    {
        if (
            !\is_numeric($journal) &&
            (
                !$this->isEntity($journal) ||
                $journal->getId() == null
            )
        )
            return;

        Registry::get('IssuesDAO')->dumpToJson(array(
            'journal_id' => \is_numeric($journal)
                ? (int) $journal
                : $journal->getId()
        ));

        foreach (Registry->get('FileSystemManager')->listdir(
            $this->getEntityDataDir('issues')
        ) as $filename) {
            if (!$this->getIssueData($filename))
                return false;
            
            // TODO: log the error and continue the exportation
        }

        return true;
    }
}
