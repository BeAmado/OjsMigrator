<?php

namespace BeAmado\OjsMigrator\Entity;
use \BeAmado\OjsMigrator\Registry;

class SubmissionKeywordHandler extends EntityHandler
{
    protected function smHr()
    {
        return Registry::get('SubmissionHandler');
    }

    protected function tableName($name)
    {
        return $this->smHr()->formTableName($name);
    }

    protected function importKeywordList($data)
    {
        if (!$data->hasAttribute('keyword_id'))
            return false;

        if (Registry::get('DataMapper')->isMapped(
            $this->tableName('search_keyword_list'),
            $data->get('keyword_id')->getValue()
        ))
            return true;

        return $this->importEntity(
            $data,
            $this->tableName('search_keyword_list'),
            array(),
            true
        );
    }

    protected function importSubmissionSearchObjectKeyword($data)
    {
        if (!$data->hasAttribute('keyword_list'))
            return false;

        return $this->importKeywordList($data->get('keyword_list')) &&
            $this->importEntity(
                $data,
                $this->tableName('search_object_keywords'),
                array(
                    $this->tableName('search_objects') => 'object_id',
                    $this->tableName('search_keyword_list') => 'keyword_id',
                ),
                true
            );
    }

    protected function importSearchObjectKeywords($o)
    {
        if (!$o->hasAttribute('search_object_keywords'))
            return false;

        return $o->get('search_object_keywords')->forEachValue(function($sok) {
            return $this->importSubmissionSearchObjectKeyword($sok);
        });
    }

    protected function importSubmissionSearchObject($data)
    {
        if (
            !$data->hasAttribute('object_id') ||
            !$data->hasAttribute('search_object_keywords')
        )
            return false;

        if (Registry::get('DataMapper')->isMapped(
            $this->tableName('search_objects'),
            $data->get('object_id')->getValue()
        ))
            return $this->importSearchObjectKeywords($data);

        return $this->importEntity(
            $data,
            $this->tableName('search_objects'),
            array(
                $this->smHr()->formTableName() => $this->smHr()->formIdField(),
            ),
            true
        ) && $this->importSearchObjectKeywords($data);
    }

    public function importKeywords($submission)
    {
        if (!$submission->hasAttribute('keywords'))
            return false;
        
        return $submission->get('keywords')->forEachValue(function($o) {
            return $this->importSubmissionSearchObject($o);
        });
    }

    public function getSubmissionKeywords($submissionId)
    {
        $searchObjects = $this->smHr()->getDAO('search_objects')->read(array(
            
        ));
    }
}
