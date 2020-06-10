<?php

namespace BeAmado\OjsMigrator\Extension;
use \BeAmado\OjsMigrator\Registry;

class XmlDataMappingProcessor
{
    /**
     * @var string
     */
    protected $xmlMappingDir;

    /**
     * @var string
     */
    protected $dataMappingFilename;

    public function __construct($dataMappingFilename, $dir)
    {
        $this->dataMappingFilename = $dataMappingFilename;
        $this->xmlMappingDir = $dir;
    }

    /**
     * Forms the xml filename where the entity's id mapping will be stored
     *
     * @param string $entity
     * @return string
     */
    protected function formMappingFilename($entity)
    {
        return \implode(\BeAmado\OjsMigrator\DIR_SEPARATOR ?: '/', array(
            $this->xmlMappingDir,
            \implode('.', array($entity, 'xml')),
        ));
    }

    protected function getIdField($entity)
    {
        switch (\strtolower($entity)) {
            case 'controlled_vocab_entries':
                return 'controlled_vocab_entry_id';
            case 'article_files':
            case 'submission_files':
                return 'file_id';
            case 'article_supplementary_files':
            case 'submission_supplementary_files':
                return 'supp_id';
            case 'review_assignments':
                return 'review_id';
            case 'edit_assignments':
                return 'edit_id';
            case 'article_search_objects':
            case 'submission_search_objects':
                return 'object_id';
            case 'article_comments':
            case 'submission_comments':
                return 'comment_id';
            case 'article_search_keyword_list':
            case 'submission_search_keyword_list':
                return 'keyword_id';
        }

        return \implode('_', array(
            \strtolower(\substr($entity, 0, -1)),
            'id',
        ));

    }

    protected function getXmlMappingText()
    {
        return \file_get_contents($this->dataMappingFilename);
    }

    /**
     * Forms a tag with the entity id or the specified field
     *
     * @param string $entity
     * @param boolean $close _default_ false
     * @param string $field _optional_
     * @return string
     */
    protected function entityTag(
        $entity,
        $close = false,
        $field = null
    ) {
        return \implode(array(
            '<',
            $close ? '/' : '',
            $field ?: $this->getIdField($entity),
            '>',
        ));
    }

    /**
     * Gets the indexes of the borders delimiting the entity mapping in the
     * xml mappings file.
     *
     * @param string $entity
     * @param string $field _optional_
     * @return array ```['beginning' => {begin_border}, 'end' => {end_border}]```
     */
    protected function getMappingBorders($entity, $field = null)
    {
        $borders = array('beginning' => null, 'end' => null);
        $borders['beginning'] = \strpos(
            $this->getXmlMappingText(),
            $this->entityTag($entity, false, $field)
        );

        if ($borders['beginning'] !== false)
            $borders['end'] = \strpos(
                $this->getXmlMappingText(),
                $this->entityTag($entity, true, $field),
                $borders['beginning'] + \strlen(
                    $this->entityTag($entity, false, $field)
                )
            );

        return $borders;
    }

    /**
     * Writes the content into a file putting an xml version in the first line.
     *
     * @param string $entity
     * @param string $content
     * @return boolean
     */
    protected function writeXmlEntityMappingFile($entity, $content)
    {
        return \file_put_contents(
            $this->formMappingFilename($entity),
            \implode(\PHP_EOL, array(
                '<?xml version="1.0"?>', // <?
                \str_replace(\PHP_EOL, '', $content),
            ))
        );
    }

    /**
     * Extracts the id mapping into another xml file.
     *
     * @param string $entity
     * @param srting $field _optional_
     * @return boolean
     */
    public function extractXmlMapping($entity, $field = null)
    {
        $borders = $this->getMappingBorders($entity, $field);
        if (
            empty($borders) ||
            !\is_array($borders) ||
            !\array_key_exists('beginning', $borders) ||
            !\array_key_exists('end', $borders) ||
            !\array_reduce($borders, function($carry, $value) {
                return $carry && \is_numeric($value);
            }, true)
        )
            return false;
        
        return $this->writeXmlEntityMappingFile(
            $entity,
            \substr(
                $this->getXmlMappingText(),
                $borders['beginning'],
                $borders['end'] + 
                    \strlen($this->entityTag($entity, true, $field)) - 
                    $borders['beginning']
            )
        );
    }

    /**
     * Reads the mappings xml file previously created for the entity
     *
     * @param string $entity
     * @return \BeAmado\OjsMigrator\MyObject
     */
    protected function readXmlMappingForEntity($entity)
    {
        return Registry::get('XmlHandler')->createFromFile(
            $this->formMappingFilename($entity)
        );
    }

    /**
     * Gets the object representing the xml mapping and turns it into an array
     *
     * @param string $entity
     * @param string $field _optional_
     * @return array
     */
    public function getMappingsAsArray($entity, $field = null)
    {
        try {
            if (!Registry::get('FileSystemManager')->fileExists(
                $this->formMappingFilename($entity)
            ))
                $this->extractXmlMapping($entity, $field);
    
            return \array_map(function($mappingNode) {
                $mapping = array();
                foreach ($mappingNode['children'] as $m) {
                    if ($m['name'] === 'old')
                        $mapping['old'] = $m['text'];
                    else if ($m['name'] === 'new')
                        $mapping['new'] = $m['text'];
                }
    
                return $mapping;
            }, $this->readXmlMappingForEntity($entity)->get('children')
                                                      ->toArray());
        } catch (\Exception $e) {
            Registry::get('IoManager')->writeToStdout($e->getMessage());
            return array();
        }
    }

}
