<?php

namespace BeAmado\OjsMigrator;

class DataMappingManager
{
    protected function getDataMappingBaseDir()
    {
        return Registry::get('FileSystemManager')->formPathFromBaseDir(
            '.data_mapping'
        );
    }

    protected function formDataMappingDirPathForJournal($journal)
    {
        return Registry::get('FileSystemManager')->formPath(array(
            $this->getDataMappingBaseDir(),
            \implode('-', array(
                $journal->getId(),
                $journal->getData('path'),
            ))
        ));
    }

    protected function createDataMappingDirForJournal($journal)
    {
        return Registry::get('FileSystemManager')->createDir(
            $this->formDataMappingDirPathForJournal($journal)
        );
    }

    protected function journalDataMappingDirExists($journal)
    {
        return Registry::get('FileSystemManager')->dirExists(
            $this->formDataMappingDirPathForJournal($journal)
        );
    }

    protected function isJournal($journal)
    {
        return Registry::get('EntityHandler')->isEntity($journal) &&
            $journal->getTableName() === 'journals';
    }

    public function setDataMappingDir($journal)
    {
        if (!$this->isJournal($journal))
            return;
            // TODO: treat better, maybe throw an exception

        if (!$this->journalDataMappingDirExists($journal))
            $this->createDataMappingDirForJournal($journal);

        Registry::set(
            'DataMappingDir',
            $this->formDataMappingDirPathForJournal($journal)
        );
    }

    public function getDataMappingDir()
    {
        return Registry::get('DataMappingDir');
    }
}
