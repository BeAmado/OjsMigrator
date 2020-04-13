<?php

namespace BeAmado\OjsMigrator;

class MigrationManager
{
    /**
     * @var array
     */
    private $validMigrationOptions;

    public function __construct()
    {
        $this->setValidMigrationOptions();

        foreach ($this->validMigrationOptions->listKeys() as $option) {
            if (!$this->migrationOptionIsSet($option))
                $this->setMigrationOption(
                    $option,
                    (new Factory())->create(
                        $this->validMigrationOptions->get($option)
                                                    ->get('type')->getValue()
                    )
                );
        }
    }
    
    protected function migrationOptionsCreated()
    {
        return Registry::hasKey('MigrationOptions');
    }

    protected function migrationOptionIsSet($name)
    {
        return $this->migrationOptionsCreated() && 
            $this->getMigrationOptions()->hasAttribute($name);
    }

    protected function setValidMigrationOptions()
    {
        $this->validMigrationOptions = Registry::get('MemoryManager')->create(
            array(
                'action' => array(
                    'type' => 'string',
                ),
                'entitiesToMigrate' => array(
                    'type' => 'MyObject',
                ),
                'chosenJournal' => array(
                    'type' => 'MyObject',
                ),
            )
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
        if (!$this->validMigrationOptions->hasAttribute($name))
            return false;

        switch ($this->validMigrationOptions->get($name)
                                            ->get('type')->getValue()) {
            case 'string':
                return \is_string($value);
            case 'int':
            case 'integer':
                return \is_int($value);
            case 'number':
                return \is_numeric($value);
            case 'array':
                return \is_array($value);
            case 'MyObject':
                return \is_a($value, \BeAmado\OjsMigrator\MyObject::class);
        }
    }

    public function getMigrationOption($name)
    {
        if (!$this->validMigrationOptions->hasAttribute($name))
            return;

        if (\in_array(
            \strtolower($this->validMigrationOptions->get($name)
                                                    ->get('type')->getValue()),
            array('array', 'myobject')
        ))
            return $this->getMigrationOptions()->get($name);

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

    public function chooseEntitiesToMigrate($possibleEntities)
    {
        Registry::get('IoManager')->writeToStdout(
            PHP_EOL . '--------- Entities to be '
            . $this->getMigrationOption('action')
            . 'ed -----------' . PHP_EOL
        );

        $this->setMigrationOption(
            'entitiesToMigrate',
            Registry::get('MemoryManager')->create()
        );

        foreach ($possibleEntities as $entity) {
            if (Registry::get('ChoiceHandler')->binaryChoice(
                PHP_EOL 
                . \ucfirst($this->getMigrationOption('action'))
                . ' the ' . $entity . '?'
            ))
                $this->getMigrationOption('entitiesToMigrate')->push($entity);
        }
    }

    public function getEntitiesToMigrate()
    {
        return $this->getMigrationOption('entitiesToMigrate');
    }

    public function chooseJournal()
    {
        $journals = Registry::get('JournalsDAO')->read();
        $jArr = array();
        for ($i = 0; $i < $journals->length(); $i++) {
            $jArr[$journals->get($i)->getId()] = $journals->get($i)
                                                          ->getData('path');
        }

        $path = '';
        for ($i = 0; $i < 10; $i++) {
            $path = Registry::get('MenuHandler')->getOption(
                $jArr,
                'Choose the journal you wish to ' 
                    . ($this->choseImport() ? 'import' : 'export'),
                'Enter the number of your choice: ',
                'Journal Selection'
            );

            if (Registry::get('MenuHandler')->confirm($path))
                break;
        }

        for ($i = 0; $i < $journals->length(); $i++) {
            if ($journals->get($i)->getData('path') !== $path)
                continue;
                
            $this->setMigrationOption(
                'chosenJournal',
                $journals->get($i)->cloneInstance()
            );
            break;
        }

        Registry::get('MemoryManager')->destroy(array(
            $path,
            $jArr,
            $journals,
        ));

        unset($path);
        unset($jArr);
        unset($journals);
    }

    public function getChosenJournal()
    {
        return $this->getMigrationOption('chosenJournal');
    }

    public function choseImport()
    {
        return \strtolower($this->getMigrationOption('action')) === 'import';
    }

    public function choseExport()
    {
        return \strtolower($this->getMigrationOption('action')) === 'export';
    }

    public function choseToMigrate($entity)
    {
        if (!$this->choseImport() && !$this->choseExport())
            return false;

        return \in_array($entity, $this->getEntitiesToMigrate()->toArray());
    }
}
