<?php

namespace BeAmado\OjsMigrator\Entity;
use \BeAmado\OjsMigrator\Registry;

class UserHandler extends EntityHandler
{
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
                : $ent->getData($ent->getData('controlled_vocab_entry_id'))
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
            $this->create('users', $res)
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
                    $this->formJsonFilename(Registry::get('user')),
                    Registry::get('user')
                );

                // replace the previous user data with the new one
                $this->setUser($res);
            }

            Registry::get('user')->get('roles')->push(
                $this->create('roles', $res)
            );
            
        });
        
        // the last iteration will not dump the user to json, so it must be
        // done now.
        $this->getUserSettingsAndInterests();
        Registry::get('JsonHandler')->dumpToFile(
            $this->formJsonFilename(Registry::get('user')),
            Registry::get('user')
        );
    }
}
