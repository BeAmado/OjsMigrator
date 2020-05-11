<?php

namespace BeAmado\OjsMigrator\Extension;
use \BeAmado\OjsMigrator\Registry;

class DataMappingTranslator
{
    private $dataMapping;
    private $fuzzyData;

    public function __construct()
    {
        $this->dataMapping = Registry::get('MemoryManager')->create();
    }

    protected function getDataMapping($name = null)
    {
        if ($name === null)
            return $this->dataMapping;

        if (\is_string($name) && $this->dataMapping->hasAttribute($name))
            return $this->dataMapping->get($name);
    }

    protected function readXmlDataMapping($filename)
    {
        return Registry::get('XmlHandler')->createFromFile($filename);
    }

    protected function loadMapping($filename)
    {
        $this->fuzzyData = $this->readXmlDataMapping($filename);
    }

    protected function setJournalMapping()
    {
        if (!isset($this->fuzzyData))
            return;

        
    }

    protected function getJournalMapping()
    {
    }
}
