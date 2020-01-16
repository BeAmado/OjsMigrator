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
                
                });
        } catch (\Exception $e) {
            // TODO: TREAT BETTER
            echo \PHP_EOL . \PHP_EOL . $e->getMessage() . \PHP_EOL . \PHP_EOL;
        }
    }

}
