<?php

namespace BeAmado\OjsMigrator\Util;
use \BeAmado\OjsMigrator\FiletypeHandler;
use \BeAmado\OjsMigrator\Registry;

class JsonHandler implements FiletypeHandler
{
    public function jsonFile($filename)
    {
        if (\is_array($filename))
            return $this->jsonFile(
                Registry::get('FileSystemManager')->formPath($filename)
            );

        if ($this->hasJsonExtension($filename))
            return $filename;

        return \implode('.', array(
            $filename,
            'json',
        ));
    }

    public function hasJsonExtension($filename)
    {
        return \strtolower(\substr($filename, -5)) === '.json';
    }

    /**
     * Creates an object from a json file
     *
     * @param $filename string
     * @return \BeAmado\OjsMigrator\MyObject
     */
    public function createFromFile($filename)
    {
        if (!$this->hasJsonExtension($filename))
            return $this->createFromFile($this->jsonFile($filename));

        return Registry::get('MemoryManager')->create(
            Registry::get('EncodingHandler')->processForImport(\json_decode(
                Registry::get('FileHandler')->read($filename),
                true
            ))
        );
    }

    /**
     * Dumps the object into a json file.
     *
     * @param $filename string
     * @param $obj \BeAmado\OjsMigrator\MyObject
     * @return boolean
     */
    public function dumpToFile($filename, $obj)
    {
        if (!$this->hasJsonExtension($filename))
            return $this->dumpToFile(
                $this->jsonFile($filename),
                $obj
            );

        return Registry::get('FileHandler')->write(
            $filename,
            \json_encode(Registry::get('EncodingHandler')->processForExport(
                $obj->toArray()
            ))
        );
    }
}
