<?php

namespace BeAmado\OjsMigrator\Extension;
use \BeAmado\OjsMigrator\Application;
use \BeAmado\OjsMigrator\Registry;

class EconomicaFixer extends Application
{
    protected function defaultXmlMappingDir()
    {
        return Registry::get('FileSystemManager')->formPathFromBaseDir(array(
            'xml_mapping'
        ));
    }

    protected function xmlMappingDirKeyName()
    {
        return 'xmlMappingDir';
    }

    protected function xmlMappingFilenameKeyName()
    {
        return 'xmlMappingFilename';
    }

    protected function setXmlMappingDir($dir = null)
    {
        return Registry::set(
            $this->xmlMappingDirKeyName(),
            \is_string($dir) ? $dir : $this->defaultXmlMappingDir()
        );
    }

    protected function getXmlMappingDir()
    {
        if (!Registry::hasKey($this->xmlMappingDirKeyName()))
            $this->setXmlMappingDir();

        return Registry::get($this->xmlMappingDirKeyName());
    }

    protected function setXmlMappingFilename($filename)
    {
        Registry::set(
            $this->xmlMappingFilenameKeyName(),
            $filename
        );
    }

    protected function getXmlMappingFilename()
    {
        return Registry::get($this->xmlMappingFilenameKeyName());
    }

    protected function translator()
    {
        return new DataMappingTranslator(
            $this->getXmlMappingFilename(),
            $this->getXmlMappingDir()
        );
    }

    public function translateXmlMapping($xmlFilename)
    {
        $this->setXmlMappingFilename($xmlFilename);
        echo "\n\nTranslating the data mapping ... ";
        var_dump($this->translator()->translateAllMappings());
        echo "Done\n\n";
    }

    protected function smHr()
    {
        return Registry::get('SubmissionHandler');
    }

    protected function smCmntHr()
    {
        return Registry::get('SubmissionCommentHandler');
    }

    protected function getJournalOldId()
    {
    }

    protected function getJournalNewId()
    {
    }

    public function exportReviewComments()
    {
        // get the submissions from the journal
        $this->smHr()->getDAO()->dumpToJson(array(
            'journal_id' => $this->getJournalOldId(),
        ));

        \array_map(function($filename) {
            $sm = Registry::get('JsonHandler')->createFromFilename($filename);
            $sm->set(
                'comments',
                $this->smCmntHr()->getSubmissionComments(
                    $this->smHr()->getSubmissionId($sm)
                )
            );
            Registry::get('JsonHandler')->dumpToFile($filename, $sm);
        }, Registry::get('FileSystemManager')->listdir(
            $this->smHr()->getEntityDataDir($this->smHr()->formTableName())
        ));
    }

    public function importReviewComments()
    {
    }
}
