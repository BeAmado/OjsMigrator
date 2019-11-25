<?php

namespace BeAmado\OjsMigrator\Entity;
use \BeAmado\OjsMigrator\Registry;

class UserHandler extends 
{
    /**
     * Gets the settings for the specified user
     *
     * @param \BeAmado\OjsMigrator\Entity\Entity $user
     * @return \BeAmado\OjsMigrator\MyObject
     */
    public function getUserSettings($user)
    {
        /*
        if (!$this->userIsValidForSearch($user))
            return;
        */

        if (
            !\is_numeric($user) &&
            (
                !\is_a($user, Entity::class) ||
                !$user->hasAttribute('user_id') ||
                $user->getData('user_id') == null
            )
        )
            return;
        
        return Registry::get('UserSettingsDAO')->read(array
            'user_id' => \is_numeric($user)
                ? (int) $user
                : $user->getData('user_id')
        ));
    }

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

    protected function getControlledVocabEntry($entry)
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

        $entries = Registry::get('ControlledVocabEntriesDAO')->read(array(
            'controlled_vocab_entry_id' => \is_numeric($entry)
                ? (int) $entry
                : $entry->getData($entry->getData('controlled_vocab_entry_id'))
        ));

        if ($entries->length() !== 1) {
            Registry::get('MemoryManager')->destroy($entries);
            unset($entries);
            return;
        }

        $entry = $entries->get(0)->cloneInstance();
        Registry::get('MemoryManager')->destroy($entries);
        unset($entries);

        $entry->set(
            'settings',
            $this->getControlledVocabEntrySettings($entry)
        );

        return $entry;
    }

    protected function getControlledVocab($entry)
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

        $controlledVocabEntry = $this->getControlledVocabEntry($entry);

        if (!$controlledVocabEntry == null)
            return;

        $vocabs = Registry::get('ControlledVocabsDAO')->read(array(
            'controlled_vocab_id' => 
                $controlledVocabEntry->getData('controlled_vocab_id')
        ));

        if ($vocabs->length() !== 1) {
            Registry::get('MemoryManager')->destroy($vocabs);
            unset($vocabs);
            return;
        }

        $vocab = $vocabs->get(0)->cloneInstance();
        Registry::get('MemoryManager')->destroy($vocabs);
        unset($vocabs);

        return $vocab;
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

        if ($interests == null) {
            Registry::get('MemoryManager')->destroy($interests);
            unset($interests);
            return;
        }

        for ($i = 0; $i < $interests->length(); $i++) {
            $interests->get(0)->set(
                'controlled_vocab',
                $this->getControlledVocab(
                    $interests->get(0)->getData('controlled_vocab_entry_id')
                )
            );
        }

        return $interests;
    }

    protected function formJsonFilename($userId)
    {
        return Registry::get('EntityHandler')->getEntityDataDir('users')
            . \BeAmado\OjsMigrator\DIR_SEPARATOR . $userId . '.json';
    }

    protected function setUser($res)
    {
        Registry::remove('user');
        Registry::set(
            'user', 
            Registry::get('EntityHandler')->create('users', $res)
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
    public function getUsersFromJournal($journal)
    {
        if (
            !\is_numeric($journal) &&
            (
                !\is_a($journal, Entity::class) ||
                !$journal->hasAttribute('journal_id')
            )
        )
            return;

        $query = 'SELECT u.*, r.journal_id, r.role_id '
            . 'FROM roles r '
            . 'INNER JOIN users u '
            .     'ON u.user_id = r.user_id '
            . 'WHERE journal_id = :selectUsersFromJournal_journalId'
            . 'ORDER BY r.user_id';

        $stmt = Registry::get('StatementHandler')->create($query);

        $bound = $stmt->bindParams(
            array('journal_id' => ':selectUsersFromJournal_journalId'),
            \is_numeric($journal)
                ? Registry::get('MemoryManager')->create(array(
                    'journal_id' => $journal
                ))
                : $journal
        );

        $executed = $stmt->execute();

        Registry::remove('user');
        Registry::set('user', null);

        $stmt->fetch(function($res) {
             
            if (Registry::get('user') === null) {
                $this->setUser($res);
            } else if (Registry::get('user')->getId() != $res['user_id']) {
                // save the previous user data in the json data dir
                $this->getUserSettingsAndInterests();
                Registry::get('JsonHandler')->dumpToFile(
                    $this->formJsonFilename(Registry::get('user')->getId()),
                    Registry::get('user')
                );

                // replace the previous user data with the new one
                $this->setUser($res);
            }

            Registry::get('user')->get('roles')->push(
                Registry::get('EntityHandler')->create('roles', $res)
            );
            
        });
        
        $this->getUserSettingsAndInterests();
        Registry::get('JsonHandler')->dumpToFile(
            $this->formJsonFilename(Registry::get('user')->getId()),
            Registry::get('user')
        );
    }
}
