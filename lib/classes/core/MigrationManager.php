<?php

namespace BeAmado\OjsMigrator;

class MigrationManager
{
    /**
     * @var array
     */
    private $validMigrationOptions;

    /**
     * @var array
     */
    private $entities;

    public function __construct()
    {
        $this->setValidMigrationOptions();
        $this->setEntities();

        foreach ($this->validMigrationOptions as $name) {
            $this->setMigrationOption($name, null);
        }
    }

    protected function setValidMigrationOptions()
    {
        $this->validMigrationOptions = array(
            'action' => array(
                'type' => 'string',
            ),
            'entitiesToExport' => array(
                'type' => 'array',
            ),
            'entitiesToImport' => array(
                'type '=> 'array',
            ),
        );
    }

    protected function setEntities()
    {
        $this->entities = array(
            'journals',
            'users',
            'review_forms',
            'sections',
            'issues',
            'submissions',
            'announcements',
            'groups',
        );
    }

    public function getMigrationOptions()
    {
        return Registry::get('MigrationOptions');
    }
    
    public function getMigrationOptionsAsArray()
    {
        return Registry::get('MigrationOptions')->toArray();
    }

    protected function isValidMigrationOption($name, $value)
    {
        if (!\array_key_exists(
            \strtolower($name), 
            $this->validMigrationOptions
        ))
            return false;

        switch ($this->validMigrationOptions[$name]['type']) {
            case 'string':
                return \is_string($value);
            case 'int':
            case 'integer':
                return \is_int($value);
            case 'number':
                return \is_numeric($value);
            case 'array':
                return \is_array($value);
        }
    }

    public function getMigrationOption($name)
    {
        if (\array_key_exists($name, $this->validMigrationOptions))
            return $this->getMigrationOptions()->get($name)->getValue();
    }

    protected function setMigrationOption($name, $value)
    {
        if (!Registry::hasKey('MigrationOptions'))
            Registry::set(
                'MigrationOptions',
                Registry::get('MemoryManager')->create(array())
            );
        
        if ($this->isValidMigrationOption($name, $value))
            Registry::get('MigrationOptions')->set($name, $value);
    }

    protected function getImportExportOption()
    {
        return Registry::get('MenuHandler')->getOption(
            array(
                '1' => 'Export',
                '2' => 'Import',
                '0' => 'Exit',
            ),
            'Select which action you wish the program to perform:',
            'Enter your choice: ',
            'Export or Import'
        );
    }

    public function setImportExportAction()
    {
        $this->setMigrationOption(
            'action',
            $this->getImportExportOption()
        );
    }

    protected function chooseEntitiesToExport()
    {
        Registry::get('IoManager')->writeToStdout(
            PHP_EOL . '--------- Entities to be exported -----------' . PHP_EOL
        );

        $this->setMigrationOption(
            'entitiesToExport',
            Registry::get('MemoryManager')->create()
        );

        foreach ($this->entities as $entity) {
            if (Registry::get('ChoiceHandler')->binaryChoice(
                PHP_EOL . 'Export the ' . $entity . '?'
            ))
                $this->getMigrationOption('entitiesToExport')->push($entity);
        }
    }

    protected function chooseEntitiesToImport()
    {
        Registry::get('IoManager')->writeToStdout(
            PHP_EOL . '--------- Entities to be imported -----------' . PHP_EOL
        );

        $this->setMigrationOption(
            'entitiesToImport',
            Registry::get('MemoryManager')->create()
        );

        foreach ($this->entities as $entity) {
            if (Registry::get('ChoiceHandler')->binaryChoice(
                PHP_EOL . 'Import the ' . $entity . '?'
            ))
                $this->getMigrationOption('entitiesToImport')->push($entity);
        }
    }

}
