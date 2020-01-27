<?php

namespace BeAmado\OjsMigrator;

class UserMock extends EntityMock
{
    /**
     * @Override
     */
    public function __construct($name = null)
    {
        parent::__construct('users');
    }

    public function getUser($name)
    {
        $filename = $this->formFilename(\strtr(
            \strtolower($name),
            array(
                'batman' => 'wayne',
                'ironman' => 'stark',
                'hulk' => 'banner',
                'hawkeye' => 'barton',
                'greenlantern' => 'jordan',
                'thor' => 'blake',
            )
        ));

        if (Registry::get('FileSystemManager')->fileExists($filename))
            return Registry::get('UserHandler')->create(include($filename));
    }
}
