<?php

namespace BeAmado\OjsMigrator\Entity;
use \BeAmado\OjsMigrator\Registry;

class SubmissionKeywordHandler extends EntityHandler
{
    protected function smHr()
    {
        return Registry::get('SubmissionHandler');
    }

    protected function tableName($name = null)
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

    protected function objectFieldsToMap($data)
    {
        if (
            $data->hasAttribute('assoc_id') && 
            $data->get('assoc_id')->getValue() > 0
        )
            return array(
                $this->tableName() => $this->smHr()->formIdField(),
                $this->tableName('files') => 'assoc_id',
            );

        return array(
            $this->tableName() => $this->smHr()->formIdField(),
        );
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
            $this->objectFieldsToMap($data),
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

    protected function getSearchObjectKeywords($objectId)
    {
        $objKeywords = $this->smHr()->getDAO('search_object_keywords')
                                    ->read(array(
            'object_id' => $objectId,
        ));

        $objKeywords->forEachValue(function($o) {
            $o->set(
                'keyword_list',
                $this->smHr()->getDAO('search_keyword_list')->read(array(
                    'keyword_id' => $o->getData('keyword_id'),
                ))
            );
        });

        return $objKeywords;
    }

    public function getSubmissionKeywords($submissionId)
    {
        $searchObjects = $this->smHr()->getDAO('search_objects')->read(array(
            $this->tableName('search_objects') => $submissionId,
        ));

        $searchObjects->forEachValue(function($o) {
            $o->set(
                'search_object_keywords',
                $this->getSearchObjectKeywords($o->getId())
            );
        });

        return $searchObjects;
    }
}
