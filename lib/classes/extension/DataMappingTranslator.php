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

    /**
     * Tests if the structure of the mapping array conforms to:
     * ```['old' => {old_data}, 'new' => {new_data}]```
     *
     * @param array $mapping
     * @return boolean
     */
    protected function mappingOk($mapping)
    {
        return \is_array($mapping) &&
            \array_key_exists('old', $mapping) &&
            \is_numeric($mapping['old']) && $mapping['old'] > 0 &&
            \array_key_exists('new', $mapping) &&
            \is_numeric($mapping['new']) && $mapping['new'] > 0;
    }

    /**
     * Gets the mapping for the entity, occasionally getting by the specified 
     * field.
     *
     * @param string $entity
     * @param string $field _optional_
     * @return array
     */
    protected function getMapping($entity, $field = null)
    {
        return $this->xmlProcessor->getMappingsAsArray($entity, $field);
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
            $this->getMapping($entity),
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

    /**
     * Gets the journal old and new ids and paths, as defined in the xml 
     * mappings file using the fields **journal_id** and **journal_path**.
     *
     * @return array ```[
     *     'old' => ['journal_id' => {old_id}, 'path' => {old_path}],
     *     'new' => ['journal_id' => {new_id}, 'path' => {new_path}]
     * ]```
     */
    protected function getJournalData()
    {
        $idMapping = $this->getMapping('journals')[0];
        $pathMapping = $this->getMapping('paths', 'journal_path')[0];

        return array(
            'old' => array(
                'journal_id' => $idMapping['old'],
                'path' => $pathMapping['old'],
             ),
            'new' => array(
                'journal_id' => $idMapping['new'],
                'path' => $pathMapping['new'], 
            ),
        );
    }

    /**
     * Gets the journal whose data is to be mapped
     *
     * @return \BeAmado\OjsMigrator\Entity\Entity
     */
    public function getJournal()
    {
        return Registry::get('JournalsDAO')->read(array(
            'path' => $this->getJournalData()['new']['path'],
        ))->get(0);
    }

    /**
     * Creates a new Journal Entity with the journal new path and id.
     *
     * @return \BeAmado\OjsMigrator\Entity\Entity
     */
    protected function createJournal()
    {
        return Registry::get('JournalHandler')->create(
            $this->getJournalData()['new']
        );
    }

    protected function entitiesToMap()
    {
        return array(
            'announcements',
            'articles',
            'article_comments',
            'article_files',
            'article_galleys',
            'article_search_objects',
            'article_search_keyword_list',
            'article_supplementary_files',
            'controlled_vocabs',
            'controlled_vocab_entries',
            'edit_assignments',
            'edit_decisions',
            'groups',
            'issues',
            'journals',
            'review_assignments',
            'review_forms',
            'review_form_elements',
            'review_rounds',
            'sections',
            'users',
        );
    }

    /**
     * Gets the xml mappings for each of the entities. Returns an array with
     * the names of the entities mapped.
     *
     * @return array
     */
    protected function separateMappingsForEachEntity()
    {
        return \array_reduce(
            \array_map(function($entity) {
                return array(
                    'name' => $entity,
                    'present' => $this->xmlProcessor
                                      ->extractXmlMapping($entity),
                );
            }, $this->entitiesToMap()),
            function($carry, $elem) {
                return $elem['present']
                    ? \array_merge($carry, array($elem['name']))
                    : $carry;
            },
            array()
        );
    }

    /**
     * Maps all the entities that are in the xml mappings file, and returns an
     * array with the relatioship of the entities mapped, for instance:
     * [
     *     'sections' => true,
     *     'review_forms' => false,
     *     'users' => true,
     * ]
     * would indicate that the sections and users mappings were translated,
     * while there was some error that prevented from mapping the review_forms.
     *
     * @return array
     */
    public function translateAllMappings()
    {
        Registry::get('DataMappingManager')->setDataMappingDir(
            $this->createJournal()
        );

        return \array_reduce(
            $this->separateMappingsForEachEntity(),
            function($carry, $entity) {
                return \array_merge(
                    $carry,
                    array($entity => $this->mapData($entity))
                );
            },
            array()
        );
    }
}
