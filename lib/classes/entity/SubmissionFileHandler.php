<?php

namespace BeAmado\OjsMigrator\Entity;
use \BeAmado\OjsMigrator\Registry;

class SubmissionFileHandler extends EntityHandler
{
    /**
     * @var array
     */
    private $filesSchema;

    protected function formPath($parts = array())
    {
        if (\is_string($parts))
            return $parts;

       if (\is_array($parts))
            return \implode(\BeAmado\OjsMigrator\DIR_SEPARATOR, $parts);
    }

    protected function smHr()
    {
        return Registry::get('SubmissionHandler');
    }

    public function create($data, $name = 'files')
    {
        if (\is_a($data, \BeAmado\OjsMigrator\MyObject::class))
            return new Entity(
                $data,
                $this->smHr()->formTableName($name)
            );

        return parent::create(
            $this->smHr()->formTableName($name),
            $data
        );
    }

    public function __construct()
    {
        $this->filesSchema = array(
            'submit' => array(
                'stage' => 1,
                'abbrev' => 'SM',
                'path' => $this->formPath(array('submission', 'original')),
            ),
            'review' => array(
                'stage' => 2,
                'abbrev' => 'RV',
                'path' => $this->formPath(array('submission', 'review')),
            ),
            'edit' => array(
                'stage' => 3,
                'abbrev' => 'ED',
                'path' => $this->formPath(array('submission', 'editor')),
            ),
            'copyedit' => array(
                'stage' => 4,
                'abbrev' => 'CE',
                'path' => $this->formPath(array('submission', 'copyedit')),
            ),
            'layoutedit' => array(
                'stage' => 5,
                'abbrev' => 'LE',
                'path' => $this->formPath(array('submission', 'layout')),
            ),
            'supplementary' => array(
                'stage' => 6,
                'abbrev' => 'SP',
                'path' => 'supp',
            ),
            'publish' => array(
                'stage' => 7,
                'abbrev' => 'PB',
                'path' => 'public',
            ),
            'note' => array(
                'stage' => 8,
                'abbrev' => 'NT',
                'path' => 'note',
            ),
            'attach' => array(
                'stage' => 9,
                'abbrev' => 'AT',
                'path' => 'attachment',
            ),
        );
    }

    protected function getValidAbbrevs()
    {
        return \explode(
            ' ', 
            \trim(\array_reduce($this->filesSchema, function($carry, $item) {
                return '' . $carry . ' ' . $item['abbrev'];
            }))
        );
    }

    protected function nullSchema()
    {
        return array(
            'stage' => null,
            'abbrev' => null,
            'path' => null,
        );
    }

    protected function searchFileSchemaByName($name)
    {
        if (\array_key_exists(\strtolower($name), $this->filesSchema))
            return $this->filesSchema[\strtolower($name)];

        return $this->nullSchema();
    }

    protected function searchFileSchemaByStage($stage)
    {
        foreach ($this->filesSchema as $schema) {
            if ((int) $schema['stage'] == $stage)
                return $schema;
        }

        return $this->nullSchema();
    }

    protected function searchFileSchemaByAbbrev($abbrev)
    {
        foreach ($this->filesSchema as $schema) {
            if ($schema['abbrev'] === \strtoupper($abbrev))
                return $schema;
        }

        return $this->nullSchema();
    }

    public function getPathByFileAbbrev($abbrev)
    {
        return $this->searchFileSchemaByAbbrev($abbrev)['path'];
    }

    public function getPathByFileStage($stage)
    {
        return $this->searchFileSchemaByStage($stage)['path'];
    }

    protected function formTableName()
    {
        return $this->smHr()->formTableName('files');
    }

    protected function getDAO()
    {
        return $this->smHr()->getDAO('files');
    }

    protected function getMappedFileName($filename)
    {
        $parts = \explode('-', $filename);
        $parts[0] = Registry::get('DataMapper')->getMapping(
            $this->smHr()->formTableName(),
            $parts[0]
        );

        $parts[1] = Registry::get('DataMapper')->getMapping(
            $this->formTableName(),
            $parts[1]
        );

        if ($parts[0] == null || $parts[1] == null)
            return null;

        return \implode('-', $parts);
    }

    public function getSubmissionFileByName($filename, $map = false)
    {
        if ($map)
            $filename = $this->getMappedFileName($filename);
        
        $files = $this->getDAO()->read(array(
            'file_name' => $filename,
        ));

        if (
            !\is_a($files, \BeAmado\OjsMigrator\MyObject::class) ||
            $files->length() < 1
        )
            return null;
        
        return $files->get(0);
    }

    public function fileStageOk($file)
    {
        return $file->hasAttribute('file_stage') &&
            \is_numeric($file->get('file_stage')->getValue()) &&
            $file->get('file_stage')->getValue() >= 1 &&
            $file->get('file_stage')->getValue() <= 9;
    }

    protected function getAbbrevFromFileName($filename)
    {
        return \explode(
            '.',
            \explode('-', $filename)[3]
        )[0];
    }
    
    protected function abbrevIsValid($filename)
    {
        return \in_array(
            $this->getAbbrevFromFileName($filename),
            $this->getValidAbbrevs()
        );
    }

    protected function fileNameIsValid($filename)
    {
        return \count(\explode('-', $filename)) === 4 &&
            $this->abbrevIsValid($filename);
    }

    public function fileNameOk($file)
    {
        return $file->hasAttribute('file_name') &&
            \is_string($file->get('file_name')->getValue()) &&
            \count(\explode('-', $file->get('file_name')->getValue())) === 4 &&
            \in_array(
                $this->getAbbrevFromFileName(
                    $file->get('file_name')->getValue()
                ),
                $this->getValidAbbrevs()
            );
    }
    
    protected function getJournalSubmissionsDir($journal)
    {
        return Registry::get('JournalHandler')->getSubmissionsDir($journal);
    }

    protected function formPathByFileStage($file, $journal)
    {
        return $this->formPath(array(
            $this->getJournalSubmissionsDir($journal),
            $file->get($this->smHr()->formIdField())->getValue(),
            $this->getPathByFileStage($file->get('file_stage')->getValue()),
            $file->get('file_name')->getValue(),
        ));
    }

    protected function getPathByFileName($filename)
    {
        return $this->getPathByFileAbbrev(
            $this->getAbbrevFromFileName($filename)
        );
    }

    protected function formPathByFileName($file, $journal)
    {
        return $this->formPath(array(
            $this->getJournalSubmissionsDir($journal),
            \is_string($file) 
                ? \explode('-', $file)[0]
                : $file->get($this->smHr()->formIdField())->getValue(),
            $this->getPathByFileName(
                \is_string($file) ? $file : $file->get('file_name')->getValue()
            ),
            \is_string($file)
                ? $file
                : $file->get('file_name')->getValue(),
        ));
    }

    protected function updateFileNameInDatabase($filename)
    {
        return $this->getDAO()->update(array(
            'set' => array(
                'file_name' => $filename,
            ),
            'where' => array(
                'file_id' => \explode('-', $filename)[0],
                'revision' => \explode('-', $filename)[2],
            ),
        ));
    }

    protected function formFilePathInEntitiesDir($filename)
    {
        return $this->formPath(array(
            $this->getEntityDataDir($this->smHr()->formTableName()),
            \explode('-', $filename)[0], // submission_id
            $filename,
        ));
    }

    protected function copyFileToJournalSubmissionsDir(
        $filename,
        $journal,
        $mapFilename = false
    ) {
        return Registry::get('FileSystemManager')->copyFile(
            $this->formFilePathInEntitiesDir($filename),
            $this->formPathByFileName(
                $mapFilename ? $this->getMappedFileName($filename) : $filename, 
                $journal
            )
        );
    }

    public function importSubmissionFile($file, $journal)
    {
        return $this->importEntity(
            $file,
            $file->getTableName(),
            array(
                $this->formTableName() => 'source_file_id',
                $this->smHr()->formTableName() => $this->smHr()->formIdField(),
            )
        ) &&
        $this->updateFileNameInDatabase($this->getMappedFileName(
            $file->get('file_name')->getValue()
        )) &&
        $this->copyFileToJournalSubmissionsDir(
            $file->get('file_name')->getValue(),
            $journal,
            true // map the file_name
        );
    }

    public function copyFileFromJournalIntoEntitiesDir()
    {}
}
