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
        $this->setSubmissionAlias();
    }

    protected function setSubmissionAlias()
    {
        if (Registry::get('SchemaHandler')->tableIsDefined('articles'))
            $this->alias = 'article';

        if (Registry::get('SchemaHandler')->tableIsDefined('submissions'))
            $this->alias = 'submission';
    }

    public function getSubmissionAlias()
    {
        if (!isset($this->alias) || $this->alias == null)
            $this->setSubmissionAlias();

        return $this->alias;
    }

    protected function formTableName($name)
    {
        switch(Registry::get('CaseHandler')->transformCaseTo('lower', $name)) {
            case 'settings':
                return \implode('_', array(
                    $this->alias . 's',
                    'settings',
                ));
        }
    }
}
