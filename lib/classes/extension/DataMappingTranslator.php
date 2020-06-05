<?php

namespace BeAmado\OjsMigrator\Extension;
use \BeAmado\OjsMigrator\Registry;

class DataMappingTranslator
{
    /**
     * @var \BeAmado\OjsMigrator\Extension\XmlDataMappingProcessor
     */
    protected $xmlProcessor;

    /**
     * Initializes the instance creating an XmlDataMappingProcessor.
     *
     * @param string $dataMappingFilename > The xml file where all the data
     * mappings for a journal was previously stored.
     * @param string $xmlMappingDir > The directory where the xml mapping files
     * will be stored.
     */
    public function __construct($dataMappingFilename, $xmlMappingDir)
    {
        $this->xmlProcessor = new XmlDataMappingProcessor(
            $dataMappingFilename,
            $xmlMappingDir
        );
    }

    protected function mappingOk($mapping)
    {
        return \is_array($mapping) &&
            \array_key_exists('old', $mapping) &&
            \is_numeric($mapping['old']) && $mapping['old'] > 0 &&
            \array_key_exists('new', $mapping) &&
            \is_numeric($mapping['new']) && $mapping['new'] > 0;
    }

    /**
     * Maps the ids that are in the object which was loaded from an xml 
     * mapping file.
     *
     * @param string $entity
     * @return boolean
     */
    public function mapData($entity)
    {
        return \array_reduce(
            $this->xmlProcessor->getMappingsAsArray($entity),
            function($c, $mapping) {
                if (!$this->mappingOk($mapping) || !$c[0])
                    return array(false, null);

                $c[0] = Registry::get('DataMapper')->mapData(
                    $c[1], // the entity name
                    $mapping
                );

                return $c;
            },
            array(true, $entity)
        )[0];
    }
}
