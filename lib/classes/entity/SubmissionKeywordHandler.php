<?php

namespace BeAmado\OjsMigrator\Entity;
use \BeAmado\OjsMigrator\Registry;

class SubmissionKeywordHandler extends EntityHandler
{
    protected function smHr()
    {
        return Registry::get('SubmissionHandler');
    }

    protected function searchTypes()
    {
        return array(
            'author'      => 0x001,
            'title'       => 0x002,
            'abstract'    => 0x004,
            'discipline'  => 0x008,
            'subject'     => 0x010,
            'type'        => 0x020,
            'coverage'    => 0x040,
            'galley_file' => 0x080,
            'index_terms' => 0x078,
            'supplementary_file'          => 0x100,
            'supplementary_file_metadata' => 0x300,
        );
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
            return true;
//            return $this->importSearchObjectKeywords($data);

        return $this->importEntity(
            $data,
            $this->tableName('search_objects'),
            $this->objectFieldsToMap($data),
            true
        ) && $this->importSearchObjectKeywords($data);
    }

    protected function formSearchObjectsDir($submission)
    {
        return Registry::get('FileSystemManager')->formPath(array(
            $this->smHr()->formSubmissionEntityDataDir($submission),
            'search_objects',
        ));
    }

    public function importKeywords($submission, $importFromFiles = true)
    {
        if (
            !$importFromFiles && 
            $this->isMyObject($submission) && 
            $submission->hasAttribute('keywords')
        )
            return $submission->get('keywords')->forEachValue(function($obj) {
                return $this->importSubmissionSearchObject($obj);
            });

        return \array_reduce(
            Registry::get('FileSystemManager')->listdir(
                $this->formSearchObjectsDir($submission)
            ) ?: array(),
            function($carry, $filename) {
                return $carry && $this->importSubmissionSearchObject(
                    Registry::get('JsonHandler')->createFromFile($filename)
                );
            },
            true
        );
    }

    protected function getSearchObjectKeywords($objectId)
    {
        $objKeywords = $this->smHr()
                            ->getDAO('search_object_keywords')->read(array(
            'object_id' => $objectId,
        ));

        if ($this->isMyObject($objKeywords))
            $objKeywords->forEachValue(function($o) {
                $o->set(
                    'keyword_list',
                    $this->smHr()->getDAO('search_keyword_list')->read(array(
                        'keyword_id' => $o->getData('keyword_id'),
                    ))->get(0)
                );
            });

        return $objKeywords;
    }

    public function getSubmissionKeywords($submissionId)
    {
        $searchObjects = $this->smHr()->getDAO('search_objects')->read(array(
            $this->smHr()->formIdField() => $submissionId,
        ));

        if ($this->isMyObject($searchObjects))
            $searchObjects->forEachValue(function($o) {
                $o->set(
                    'search_object_keywords',
                    $this->getSearchObjectKeywords($o->getId())
                );
            });

        return $searchObjects;
    }

    protected function getObjectId($searchObject)
    {
        if (
            $this->isMyObject($searchObject) &&
            $searchObject->hasAttribute('object_id')
        )
            return $searchObject->get('object_id')->getValue();
    }

    public function formSearchObjectFilename($searchObject)
    {
        return Registry::get('JsonHandler')->jsonFile(array(
            $this->smHr()->formSubmissionEntityDataDir(
                $this->smHr()->getSubmissionId($searchObject)
            ),
            'search_objects',
            $this->getObjectId($searchObject),
        ));
    }

    protected function dumpSearchObject($searchObject)
    {
        return Registry::get('JsonHandler')->dumpToFile(
            $this->formSearchObjectFilename($searchObject),
            $searchObject
        );
    }

    public function exportSearchObjects($submissionId)
    {
        $searchObjects = $this->getSubmissionKeywords($submissionId);

        if (
            $this->isMyObject($searchObjects) && 
            $searchObjects->length() > 0
        )
            return $searchObjects->forEachValue(function($obj) {
                return $this->dumpSearchObject($obj);
            });
    }
}
