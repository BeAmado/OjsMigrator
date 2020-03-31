<?php

namespace BeAmado\OjsMigrator\Test;
use \BeAmado\OjsMigrator\Registry;

class UserMock extends EntityMock
{
    use JournalFiller;

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

        if (!Registry::get('FileSystemManager')->fileExists($filename))
            return;

        $user = Registry::get('UserHandler')->create(include($filename));

        if ($user->hasAttribute('roles'))
            $user->get('roles')->forEachValue(function($role) {
                $this->fillJournalId($role);
            });
        
        return $user;
    }
}
