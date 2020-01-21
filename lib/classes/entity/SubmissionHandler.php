<?php

namespace BeAmado\OjsMigrator\Entity;
use \BeAmado\OjsMigrator\Registry;

class SubmissionHandler extends EntityHandler
{
    /**
     * @var string
     */
    protected $alias;
    
    public function __construct()
    {
        //parent::__construct();
        $this->setEntityAlias();
    }

    protected function setEntityAlias()
    {
        if (Registry::get('SchemaHandler')->tableIsDefined('articles'))
            $this->alias = 'article';

        if (Registry::get('SchemaHandler')->tableIsDefined('submissions'))
            $this->alias = 'submission';
    }

    public function getEntityAlias()
    {
        if (!isset($this->alias) || $this->alias == null)
            $this->setEntityAlias();

        return $this->alias;
    }

    public function formTableName($name)
    {
        /*switch(Registry::get('CaseHandler')->transformCaseTo('lower', $name)) {
            case 'settings':
                return \implode('_', array(
                    $this->alias . 's',
                    'settings',
                ));
        }*/

        if (
            $name == null || 
            \in_array(\strtolower($name), array(
                'main', 'article', 'articles', 'submission', 'submissions'
            ))
        )
            return $this->getEntityAlias() . 's';
        else if (!\in_array(\strtolower(\explode('_', $name)[0]), array(
            'article', 'submission'
        )))
            return $this->getEntityAlias() . '_' . $name;

        $parts = explode('_', $name);

        $parts[0] = $this->getEntityAlias();

        return \implode('_', $parts);
    }

    public function formIdField()
    {
        return $this->getEntityAlias() . '_id';
    }

    public function getDAO($name = 'main')
    {
        return Registry::get(
            Registry::get('CaseHandler')->transformCaseTo(
                'Pascal',
                $this->formTableName($name)
            ) . 'DAO'
        );
    }

    protected function registerSubmission($data)
    {
        $submission = $this->getValidData($this->formTableName(), $data);
        if (!$this->setMappedData($submission, array(
            'users' => 'user_id',
            'sections' => 'section_id',
            'journals' => 'journal_id',
        )))
            return false;

        return $this->createInDatabase($submission);
    }

    protected function importSubmissionSetting($data)
    {
        $setting = $this->getValidData(
            $this->formTableName('settings'),
            $data
        );

        if (!$this->setMappedData($setting, array(
            $this->formTableName() => $this->formIdField(), 
        )))
            return;

        return $this->createOrUpdateInDatabase($setting);
    }

    protected function importSubmissionFile($data)
    {
        $submissionFile = $this->getValidData(
            $this->formTableName('files'),
            $data
        );

        if (!$this->setMappedData($submissionFile, array(
            $this->formTableName() => $this->formIdField(),
        )))
            return;

        return $this->createOrUpdateInDatabase($submissionFile);
    }

    protected function importSubmissionSupplementaryFile($data)
    {
        $suppFile = $this->getValidData(
            $this->formTableName('supplementary_files'),
            $data
        );

        if (!$this->setMappedData($suppFile, array(
            $this->formTableName('files') => 'file_id',
            $this->formTableName() => $this->formIdField(),
        )))
            return;

        return $this->createOrUpdateInDatabase($suppFile);
    }

    protected function importSubmissionSuppFileSetting($data)
    {
        return $this->importEntity(
            $data,
            $this->formTableName('supp_file_settings'),
            array($this->formTableName('supplementary_files') => 'supp_id')
        );
    }

    protected function importSubmissionGalley($data)
    {
        throw new \Exception(
            'Gotta implement the method importSubmissionGalley '
                . ' in the class SubmissionHandler.'
        );
    }

    protected function importSubmissionGalleySetting($data)
    {
        throw new \Exception(
            'Gotta implement the method importSubmissionGalleySetting '
                . ' in the class SubmissionHandler.'
        );
    }

    protected function importSubmissionComment($data)
    {
        throw new \Exception(
            'Gotta implement the method importSubmissionComment '
                . ' in the class SubmissionHandler.'
        );
    }

    protected function importSubmissionHtmlGalleyImage($data)
    {
        throw new \Exception(
            'Gotta implement the method importSubmissionHtmlGalleyImage '
                . ' in the class SubmissionHandler.'
        );
    }

    protected function importSubmissionKeyword($data)
    {
        throw new \Exception(
            'Gotta implement the method importSubmissionKeywords '
                . ' in the class SubmissionHandler.'
        );
    }

    protected function importSubmissionAuthor($data)
    {
        throw new \Exception(
            'Gotta implement the method importSubmissionAuthor '
                . ' in the class SubmissionHandler.'
        );
    }

    protected function importEditAssingment($data)
    {

    }

    protected function importEditDecision($data)
    {

    }

    protected function importSubmissionHistory($data)
    {

    }

    protected function importReviewAssingment($data)
    {

    }

    protected function importReviewRound($data)
    {

    }

    public function importSubmission($submission)
    {
        if (
            !Registry::get('DataMapper')->isMapped(
                $this->formTableName(),
                $submission->getId()
            ) &&
            !$this->registerSubmission($submission)
        )
            return false;

        // import the submission settings
        $submission->get('settings')->forEachValue(function($setting) {
            $this->importSubmissionSetting($setting);
        });
    }

}
