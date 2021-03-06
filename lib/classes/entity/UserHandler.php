<?php

namespace BeAmado\OjsMigrator\Entity;
use \BeAmado\OjsMigrator\Registry;
use \BeAmado\OjsMigrator\ImportExport;

class UserHandler extends EntityHandler implements ImportExport
{
    public function create($data, $extra = null)
    {
        return new Entity($data, 'users');
    }

    protected function userIsAlreadyRegistered($user)
    {
        $users = Registry::get('UsersDAO')->read(array(
            'email' => $user->getData('email'),
        ));

        if (
            !\is_a($users, \BeAmado\OjsMigrator\MyObject::class) ||
            $users->length() === 0
        )
            return false;

        if (!Registry::get('DataMapper')->isMapped('users', $user->getId()))
            Registry::get('DataMapper')->mapData('users', array(
                'old' => $user->getId(),
                'new' => $users->get(0)->getId(),
            ));

        Registry::get('MemoryManager')->destroy($users);
        unset($users);
        
        return true;
    }

    protected function usernameExists($username)
    {
        $users = Registry::get('UsersDAO')->read(array(
            'username' => $username,
        ));

        if (
            \is_a($users, \BeAmado\OjsMigrator\MyObject::class) &&
            $users->length() === 1
        )
            return true;

        return false;
    }

    protected function usernameHasNumber($username)
    {
        return \is_numeric(\substr($username, -1));
    }

    protected function formNextUsername($username)
    {
        if ($username == null)
            return 'User1';

        if (!$this->usernameHasNumber($username))
            return $username . '2';

        if (\is_numeric($username))
            return '' . (((int) $username) + 1);
        
        $digits = null;
        for ($digits = 1; $digits < strlen($username); $digits++) {
            if (!\is_numeric(\substr($username, -$digits)))
                break;
        }

        return \substr($username, 0, -$digits)
            . (((int) \substr($username, -$digits)) + 1);
    }

    protected function formNewUsername($username, $exists = true)
    {
        if (!$exists)
            return $username;

        $tries = 0;
        $newUsername = '' . $username; // copy
        while ($exists && $tries < 1000) {
            $tries++;
            $newUsername = $this->formNextUsername($newUsername);
            $exists = $this->usernameExists($newUsername);
        }

        return $newUsername;
    }

    public function formChangedUserJsonFilename($username)
    {
        return Registry::get('FileSystemManager')->formPath(array(
            $this->getEntityDataDir('changed_users'),
            \implode('.', array($username, 'json')),
        ));
    }

    public function logChangedUserData($user, $oldUsername)
    {
        $changedUser = Registry::get('MemoryManager')->create(array(
            'old_username' => $oldUsername,
        ));

        foreach (array(
            'username',
            'email',
            'first_name',
            'middle_name',
            'last_name',
        ) as $attr) {
            $changedUser->set($attr, $user->getData($attr));
        }

        return Registry::get('JsonHandler')->dumpToFile(
            $this->formChangedUserJsonFilename($user->getData('username')),
            $changedUser
        );
    }

    protected function formNewUsernameAndLogTheChange($user)
    {
        $oldUsername = $user->getData('username');
        $user->set(
            'username',
            $this->formNewUsername($user->getData('username'))
        );

        $this->logChangedUserData($user, $oldUsername);
        unset($oldUsername);
    }

    protected function registerUser($data)
    {
        $user = $this->getValidData('users', $data);
        if ($this->usernameExists($user->getData('username')))
            $this->formNewUsernameAndLogTheChange($user);

        return $this->createInDatabase($user);
    }

    protected function importUserSetting($data)
    {
        return $this->importEntity(
            $data, 
            'user_settings', 
            array('users' => 'user_id')
        );
    }

    protected function controlledVocabIsMapped($data)
    {
        return Registry::get('DataMapper')->isMapped(
            'controlled_vocabs',
            $data->get('controlled_vocab_id')->getValue()
        );
    }

    protected function mapControlledVocabIfExists($data, $id = null)
    {
        if (empty($id))
            return false;

        return Registry::get('DataMapper')->mapData(
            'controlled_vocabs',
            array(
                'old' => $data->get('controlled_vocab_id')->getValue(),
                'new' => $id,
            )
        );
    }

    protected function controlledVocabExists($data)
    {
        $res = Registry::get('ControlledVocabsDAO')->read(array(
            'symbolic' => $data->get('symbolic')->getValue(),
            'assoc_id' => $data->get('assoc_id')->getValue(),
            'assoc_type' => $data->get('assoc_type')->getValue(),
        ));

        if (empty($res) || !$this->isMyObject($res) || $res->length() < 1)
            return Registry::get('MemoryManager')->destroy($res);

        return $res->get(0)->getId();
    }

    protected function importControlledVocab($data)
    {
        if (
            !$data->hasAttribute('symbolic') ||
            !$data->hasAttribute('assoc_id') ||
            !$data->hasAttribute('assoc_type')
        )
            return false;

        if (
            $this->controlledVocabIsMapped($data) ||
            $this->mapControlledVocabIfExists(
                $data,
                $this->controlledVocabExists($data)
            )
        )
            return true;

        if ($this->controlledVocabExists($data))
            return $this->mapControlledVocab($data);

        return $this->createInDatabase(
            $this->getValidData('controlled_vocabs', $data)
        );
    }

    protected function importControlledVocabEntry($data)
    {
        if (!$data->hasAttribute('settings'))
            return false;

        $entry = $this->getValidData('controlled_vocab_entries', $data);
        
        if (!Registry::get('DataMapper')->isMapped(
            'controlled_vocabs',
            $entry->getData('controlled_vocab_id')
        ))
            $data->get('controlled_vocabs')->forEachValue(function($vocab) {
                $this->importControlledVocab($vocab);
            });

        $entry->set(
            'controlled_vocab_id',
            Registry::get('DataMapper')->getMapping(
                'controlled_vocabs',
                $entry->getData('controlled_vocab_id')
            )
        );

        if (!$this->createInDatabase($entry))
            return false;

        $data->get('settings')->forEachValue(function($s) {
            $setting = $this->getValidData(
                'controlled_vocab_entry_settings',
                $s
            );

            $setting->set(
                'controlled_vocab_entry_id',
                Registry::get('DataMapper')->getMapping(
                    'controlled_vocab_entries',
                    $setting->getData('controlled_vocab_entry_id')
                )
            );

            $this->createOrUpdateInDatabase($setting);
        });

        return true;
    }

    protected function putInterestInDatabase($interest)
    {
        $interest->set(
            'user_id',
            Registry::get('DataMapper')->getMapping(
                'users',
                $interest->getData('user_id')
            )
        );
        $interest->set(
            'controlled_vocab_entry_id',
            Registry::get('DataMapper')->getMapping(
                'controlled_vocab_entries',
                $interest->getData('controlled_vocab_entry_id')
            )
        );

        if (
            $interest->getData('user_id') == null ||
            $interest->getData('controlled_vocab_entry_id') == null
        )
            return false;

        $interests = Registry::get('UserInterestsDAO')->read($interest);
        
        if (
            !\is_a($interests, \BeAmado\OjsMigrator\MyObject::class) ||
            $interests->length() < 1
        ) {
            Registry::get('MemoryManager')->destroy($interests);
            unset($interests);
            return $this->createInDatabase($interest);
        }

        return true;
    }

    protected function importUserInterest($data)
    {
        $interest = $this->getValidData('user_interests', $data);
        if (!Registry::get('DataMapper')->isMapped(
            'users',
            $interest->getData('user_id')
        ))
            return false;

        if (Registry::get('DataMapper')->isMapped(
            'controlled_vocab_entries',
            $interest->getData('controlled_vocab_entry_id')
        ))
            return $this->putInterestInDatabase($interest);

        if (!$data->hasAttribute('controlled_vocab_entries'))
            return false;

        $data->get('controlled_vocab_entries')->forEachValue(function($entry) {
            $this->importControlledVocabEntry($entry);
        });

        return $this->putInterestInDatabase($interest);
    }

    protected function importUserRole($data)
    {
        return $this->importEntity(
            $data,
            'roles',
            array(
                'users' => 'user_id',
                'journals' => 'journal_id',
            )
        );
    }

    public function importUser($user)
    {
        try {
            if (!\is_a($user, \BeAmado\OjsMigrator\Entity\Entity::class))
                $user = $this->create($user);
    
            if ($user->getTableName() !== 'users')
                return false;
    
            if (!$this->userIsAlreadyRegistered($user))
                $this->registerUser($user);
    
            // import the settings
            if ($user->hasAttribute('settings'))
                $user->get('settings')->forEachValue(function($setting) {
                    $this->importUserSetting($setting);
                });
    
            // import the interests
            if ($user->hasAttribute('interests'))
                $user->get('interests')->forEachValue(function($interest) {
                    $this->importUserInterest($interest);
                });
    
            // import the roles
            if ($user->hasAttribute('roles'))
                $user->get('roles')->forEachValue(function($role) {
                    $this->importUserRole($role);
                });

        } catch (\Exception $e) {
            // TODO: treat the exception
            echo "\n\n" . $e->getMessage() . "\n\n";
            return false;
        }

        return true;

    }

    /**
     * Gets the settings for the specified user
     *
     * @param \BeAmado\OjsMigrator\Entity\Entity $user
     * @return \BeAmado\OjsMigrator\MyObject
     */
    public function getUserSettings($user)
    {
        if (
            !\is_numeric($user) &&
            (
                !\is_a($user, Entity::class) ||
                !$user->hasAttribute('user_id') ||
                $user->getData('user_id') == null
            )
        )
            return;
        
        return Registry::get('UserSettingsDAO')->read(array(
            'user_id' => \is_numeric($user)
                ? (int) $user
                : $user->getData('user_id')
        ));
    }

    /**
     * Gets the roles that the user has in the journal
     *
     * @param \BeAmado\OjsMigrator\Entity\Entity | integer $user
     * @param \BeAmado\OjsMigrator\Entity\Entity | integer $journal
     * @return \BeAmado\OjsMigrator\MyObject
     */
    public function getUserRoles($user, $journal)
    {
        if (
            !\is_numeric($user) &&
            (
                !\is_a($user, Entity::class) ||
                !$user->hasAttribute('user_id') ||
                $user->getData('user_id') == null
            )
        )
            return;
        
        if (
            !\is_numeric($journal) &&
            (
                !\is_a($journal, Entity::class) ||
                !$journal->hasAttribute('journal_id') ||
                $journal->getData('journal_id') == null
            )
        )
            return;
        
        return Registry::get('RolesDAO')->read(array(
            'user_id' => \is_numeric($user) 
                ? (int) $user 
                : $user->getData('user_id'),
            'journal_id' => \is_numeric($journal) 
                ? (int) $journal 
                : $journal->getData('journal_id'),
        ));
    }

    protected function getControlledVocabEntrySettings($entry)
    {
        if (
            !\is_numeric($entry) &&
            (
                !\is_a($entry, Entity::class) ||
                !$entry->hasAttribute('controlled_vocab_entry_id') ||
                $entry->getData('controlled_vocab_entry_id') == null
            )
        )
            return;

        return Registry::get('ControlledVocabEntrySettingsDAO')->read(array(
            'controlled_vocab_entry_id' => \is_numeric($entry)
                ? (int) $entry
                : $entry->getData('controlled_vocab_entry_id')
        ));
    }

    protected function getControlledVocabs($entry)
    {
        if (
            !\is_numeric($entry) &&
            (
                !\is_a($entry, Entity::class) ||
                !$entry->hasAttribute('controlled_vocab_entry_id') ||
                $entry->getData('controlled_vocab_entry_id') == null
            )
        )
            return;

        return Registry::get('ControlledVocabsDAO')->read(array(
            'controlled_vocab_id' => \is_numeric($entry)
                ? $entry
                : $entry->getData('controlled_vocab_id')
        ));
    }

    protected function getControlledVocabEntries($ent)
    {
        if (
            !\is_numeric($ent) &&
            (
                !\is_a($ent, Entity::class) ||
                !$ent->hasAttribute('controlled_vocab_entry_id') ||
                $ent->getData('controlled_vocab_entry_id') == null
            )
        )
            return;

        $entries = Registry::get('ControlledVocabEntriesDAO')->read(array(
            'controlled_vocab_entry_id' => \is_numeric($ent)
                ? (int) $ent
                : $ent->getData('controlled_vocab_entry_id')
        ));

        if (
            !\is_a($entries, \BeAmado\OjsMigrator\MyObject::class) ||
            $entries->length() < 1
        ) {
            Registry::get('MemoryManager')->destroy($entries);
            unset($entries);
            return;
        }

        $entries->forEachValue(function($entry) {
            $entry->set(
                'settings',
                $this->getControlledVocabEntrySettings($entry)
            );

            $entry->set(
                'controlled_vocabs',
                $this->getControlledVocabs($entry)
            );
        });

        return $entries;
    }

    /**
     * Gets the interest of the specified user
     *
     * @param \BeAmado\OjsMigrator\Entity\Entity $user
     * @return \BeAmado\OjsMigrator\MyObject
     */
    public function getUserInterests($user)
    {
        if (
            !\is_numeric($user) &&
            (
                !\is_a($user, Entity::class) ||
                !$user->hasAttribute('user_id') ||
                $user->getData('user_id') == null
            )
        )
            return;
        
        $interests = Registry::get('UserInterestsDAO')->read(array(
            'user_id' => (\is_numeric($user))
                ? (int) $user 
                : $user->getData('user_id')
        ));

        if (
            !\is_a($interests, \BeAmado\OjsMigrator\MyObject::class) ||
            $interests->length() < 1
        ) {
            Registry::get('MemoryManager')->destroy($interests);
            unset($interests);
            return;
        }

        $interests->forEachValue(function($interest) {
            $interest->set(
                'controlled_vocab_entries',
                $this->getControlledVocabEntries($interest)
            );
        });

        return $interests;
    }

    protected function setUser($res)
    {
        Registry::remove('user');
        Registry::set(
            'user', 
            $this->create($res)
        );
        Registry::get('user')->set('roles', array());
    }

    protected function getUserSettingsAndInterests()
    {
        Registry::get('user')->set(
            'settings',
            $this->getUserSettings(Registry::get('user')->getId())
        );

        Registry::get('user')->set(
            'interests',
            $this->getUserInterests(Registry::get('user')->getId())
        );
    }

    /**
     * Gets the users from the journal and saves the data as json files.
     *
     * @param mixed $journal
     * @return void
     */
    public function exportUsersFromJournal($journal)
    {
        if (
            !\is_numeric($journal) &&
            (
                !\is_a($journal, Entity::class) ||
                !$journal->hasAttribute('journal_id')
            )
        )
            return;

        $vars = Registry::get('MemoryManager')->create();
        $vars->set(
            'query',
            'SELECT u.*, r.journal_id, r.role_id '
          . 'FROM roles r '
          . 'INNER JOIN users u '
          .     'ON u.user_id = r.user_id '
          . 'WHERE journal_id = :selectUsersFromJournal_journalId '
          . 'ORDER BY r.user_id'
        );

        $vars->set(
            'stmt',
            Registry::get('StatementHandler')->create($vars->get('query')
                                                           ->getValue())
        );

        $vars->set(
            'bound',
            $vars->get('stmt')->bindParams(
                array('journal_id' => ':selectUsersFromJournal_journalId'),
                \is_numeric($journal)
                    ? Registry::get('MemoryManager')->create(array(
                        'journal_id' => $journal
                    ))
                    : $journal
            )
        );

        $vars->set(
            'executed',
            $vars->get('stmt')->execute()
        );

        Registry::remove('user');
        Registry::set('user', null);

        $vars->get('stmt')->fetch(function($res) {
             
            if (Registry::get('user') === null) {
                $this->setUser($res);
            } else if (Registry::get('user')->getId() != $res['user_id']) {
                // save the previous user data in the json data dir
                $this->getUserSettingsAndInterests();
                $this->dumpEntity(Registry::get('user'));

                // replace the previous user data with the new one
                $this->setUser($res);
            }

            Registry::get('user')->get('roles')->push(
                Registry::get('EntityHandler')->create('roles', $res)
            );

            return true;
            
        });

        Registry::get('MemoryManager')->destroy($vars);
        unset($vars);
        
        // the last iteration will not dump the user to json, so it must be
        // done now.
        $this->getUserSettingsAndInterests();
        $this->dumpEntity(Registry::get('user'));

        Registry::remove('user');
    }

    public function import($user)
    {
        return $this->importUser($user);
    }

    public function export($journal)
    {
        return $this->exportUsersFromJournal($journal);
    }
}
