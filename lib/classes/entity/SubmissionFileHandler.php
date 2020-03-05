<?php

namespace BeAmado\OjsMigrator\Entity;
use \BeAmado\OjsMigrator\Registry;

class SubmissionFileHandler extends EntityHandler
{
    /**
     * @var array
     */
    private $filesSchema;

    protected function formPath($dirs = array())
    {
        if (\is_string($dirs))
            return $dirs;

       if (\is_array($dirs))
            return \implode(\BeAmado\OjsMigrator\DIR_SEPARATOR, $dirs);
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
                return '' . $carry . ' ' . $item;
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

    protected function getMappedFileName($filename)
    {
        $parts = \explode('_', $filename);
        $parts[0] = Registry::get('DataMapper')->getMapping(
            Registry::get('SubmissionHandler')->formTableName(),
            $parts[0]
        );

        $parts[1] = Registry::get('DataMapper')->getMapping(
            Registry::get('SubmissionHandler')->formTableName('files'),
            $parts[1]
        );

        if ($parts[0] == null || $parts[1] == null)
            return null;

        return \implode('_', $parts);
    }

    public function getSubmissionFileByName($filename, $map = false)
    {
        if ($map)
            $filename = $this->getMappedFileName($filename);
        
        $files = Registry::get('SubmissionHandler')->getDAO('files')
                                                   ->read(array(
            'file_name' => $filename,
        ));

        if (
            !\is_a($files, \BeAmado\OjsMigrator\MyObject::class) ||
            $files->length() < 1
        )
            return null;
        
        return $files->get(0);
    }

    protected function getJournalIdFromSubmissionFile($file)
    {
        
    }

    public function fileStageOk($file)
    {
        return $file->hasAttribute('stage') &&
            \is_numeric($file->get('stage')->getValue()) &&
            $file->get('stage')->getValue() > 0;
    }

    protected function fileNameIsValid($filename)
    {
        $parts = \explode('-', $filename);
        if (\count($parts) !== 4)
            return false;

        if (!$this->abbrevIsValid(\substr($parts[3], 0, 2)))
            return false;
    }

    public function fileNameOk($file)
    {
        return $file->hasAttribute('file_name') &&
            \is_string($file->get('file_name')->getValue()) &&
            \count(\explode('-', $file->get('file_name')->getValue()) === 4 &&
            \in_array(
                \explode('-', $file->get('file_name')->getValue())[2],
                $this->getValidAbbrevs()
            );
    }

    public function formSubmissionFilePath($file, $journal)
    {
        if (\is_string($file))
            $file = $this->getSubmissionFileByName($file);

        if (
            !\is_a($file, \BeAmado\OjsMigrator\MyObject::class) ||
            !$file->hasAttribute('stage') ||
            !$file->hasAttribute('file_name')
        )
            return;

        if ($this->fileStageOk($file))
            return $this->formPath(array(
                Registry::get('JournalHandler')->getSubmissionsDir($journal),
                $this->getPathByFileStage($file->get('stage')->getValue()),
            ));
    }

    public function importSubmissionFile($file)
    {
    }

    public function exportSubmissionFile($file)
    {
    }
}
