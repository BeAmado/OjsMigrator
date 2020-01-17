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
    }

}
