<?php

namespace BeAmado\OjsMigrator\Entity;
use BeAmado\OjsMigrator\Registry;

class AssocHandler
{
    private $assocTypes;

    public function __construct()
    {
        $this->setAssocTypes();
    }

    protected function formTableName($name)
    {
        switch (\strtolower($name)) {
            case 'editor':
            case 'plugin':
            case 'workflow_stage':
                return;
            case 'supp_file':
                return Registry::get('SubmissionHandler')->formTableName(
                    'supplementary_files'
                );
            case 'submission':
                return Registry::get('SubmissionHandler')->formTableName();
        }

        return \strtolower(Registry::get('GrammarHandler')->getPlural(
            (\strpos(\strtolower($name), 'user') !== false)
                ? Registry::get('ArrayHandler')->getLast(\explode('_', $name))
                : $name
        ));
    }

    protected function setAssocTypes()
    {
        $this->assocTypes = array(
            'journal'         => 0x0000100,
            'submission'      => 0x0000101,
            'announcement'    => 0x0000102,
            'section'         => 0x0000103,
            'issue'           => 0x0000103,
            'galley'          => 0x0000104,
            'issue_galley'    => 0x0000105,
            'supp_file'       => 0x0000106,
            'user'            => 0x0001000,
            'user_group'      => 0x0100002,
            'citation'        => 0x0100003,
            'author'          => 0x0100004,
            'editor'          => 0x0100005,
            'signoff'         => 0x0100006,
            'user_role'       => 0x0100007,
            'workflow_stage'  => 0x0100008,
            'plugin'          => 0x0000211,
        );
    }

    protected function getAssocTypes()
    {
        return $this->assocTypes;
    }

    public function getAssocType($name)
    {
        if (\array_key_exists($name, $this->getAssocTypes()))
            return $this->getAssocTypes()[$name];

    }

    public function getAssocTypeSubmission()
    {
        return $this->getAssocType('submission');
    }

    public function getAssocTableName($assocType)
    {
        foreach ($this->getAssocTypes() as $name => $type) {
            if ($type == $assocType)
                return $this->formTableName($name);
        }
    }
}
