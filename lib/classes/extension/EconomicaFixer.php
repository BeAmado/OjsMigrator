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

    protected function xmlMappingDirExists()
    {
        return Registry::get('FileSystemManager')->dirExists(
            $this->getXmlMappingDir()
        );
    }

    protected function createXmlMappingDir()
    {
        return Registry::get('FileSystemManager')->createDir(
            $this->getXmlMappingDir()
        );
    }

    public function translateXmlMapping($xmlFilename)
    {
        if (!$this->xmlMappingDirExists())
            $this->createXmlMappingDir();

        $this->setXmlMappingFilename($xmlFilename);

        Registry::get('IoManager')->writeToStdout(
            'Translating the data mapping ...',
            false, // do not clear stdout
            2 // line breaks before
        );

        $this->translator()->translateAllMappings();

        Registry::get('IoManager')->writeToStdout(
            'Done',
            false, // do not clear stdout
            0, // line breaks before
            2, // line breaks after
        );
    }

    protected function smHr()
    {
        return Registry::get('SubmissionHandler');
    }

    protected function smCmntHr()
    {
        return Registry::get('SubmissionCommentHandler');
    }

    protected function journalsXmlMappingFilename()
    {
        return Registry::get('FileSystemManager')->formPath(array(
            $this->getXmlMappingDir(),
            'journals.xml',
        ));
    }

    protected function getJournalOldId()
    {
        return Registry::get('XmlHandler')
            ->createFromFile($this->journalsXmlMappingFilename())
            ->get('children') // journal_id child nodes
            ->get(0) // <mapping>
            ->get('children') // mapping child nodes which are <old> and <new>
            ->get('old')->getValue();
    }

    protected function getJournalNewId()
    {
        return Registry::get('DataMapper')->getMapping(
            'journals',
            $this->getJournalOldId()
        );
    }

    public function exportReviewComments()
    {
        (new ConnectionOverrider)->setConnection();

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

        // removes the overriding connection
        Registry::get('ConnectionManager')->closeConnection();
    }

    protected function economicaXmlMappingFilename()
    {
        return Registry::get('FileSystemManager')->formPathFromBaseDir(array(
            'economicaDataMappings.xml',
        ));
    }

    protected function dateAlreadyMapped()
    {
        return Registry::get('FileSystemManager')->dirExists(
            Registry::get('DataMappingManager')->getDataMappingDir()
        );
    }

    public function importReviewComments()
    {
        // set the data mapping directory
        Registry::get('DataMappingManager')->setDataMappingDir(
            Registry::get('MigrationManager')->getChosenJournal()
        );

        // translates the data mappings if needed
        if (!$this->dataAlreadyMapped())
            $this->translateXmlMapping($this->economicaXmlMappingFilename());

        \array_map(function($filename) {
            $this->smCmntHr()->importComments(
                Registry::get('JsonHandler')->createFromFile($filename)
            );
        }, $this->getEntityFilesToImport('submissions'));
    }

    /**
     * @Override
     */
    protected function beginFlow()
    {
        $this->showWelcomeMessage();

        Registry::get('MigrationManager')->setImportExportAction();

        if (\strtolower(
            Registry::get('MigrationManager')->getMigrationOption('action')
        ) === 'exit') {
            $this->endFlow(100);
            return;
        }
    }

    /**
     * @Override
     */
    protected function runExport()
    {
        $this->exportReviewComments();
    }

    /**
     * @Override
     */
    protected function runImport()
    {
        $this->importReviewComments();
    }
}
