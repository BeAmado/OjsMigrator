<?php

namespace BeAmado\OjsMigrator\Entity;
use \BeAmado\OjsMigrator\Registry;

class UserHandler
{
    /**
     * Checks if the data in the user is valid (enough) to search for it in 
     * the database.
     *
     * @param \BeAmado\OjsMigrator\Entity\Entity $user
     * @return boolean
     */
    protected function userIsValidForSearch($user, $validAttributes = array())
    {
        /*
        if (!\is_a($user, Entity::class))
            return false;

        $valids = array();

        foreach ($validAttributes as $attr) {
            if (!$user->hasAttribute)
                $valids[] = false;
        }

        return true;
        */
    }

    protected function getTheBestConditionToSearch(
        $user, 
        $order = array('user_id', 'email', 'username')
    ) {
        /*
        foreach ($order as $field) {
            if ($user->getData($field) != null)
                return array(
                    $field => $user->getData($field),
                );
        }
        */
    }

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
}
