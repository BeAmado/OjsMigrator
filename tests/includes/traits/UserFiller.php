<?php

namespace BeAmado\OjsMigrator\Test;

trait UserFiller
{
    /**
     * Fills the entity user_id field with the proper id.
     *
     * @param \BeAmado\OjsMigrator\MyObject
     * @param string $field
     * @return \BeAmado\OjsMigrator\MyObject 
     */
    public function fillUserId($entity, $field = 'user_id')
    {
        if (
            !\is_a($entity, \BeAmado\OjsMigrator\MyObject::class) ||
            !$entity->hasAttribute($field)
        )
            return false;

        $user = (new UserMock())->getUser(\str_replace(
            array('[', '_user', '_id', ']'),
            '', 
            $entity->get($field)->getValue()
        ));

        if (
            !\is_a($user, \BeAmado\OjsMigrator\MyObject::class) ||
            !$user->hasAttribute('user_id')
        )
            return false;

        $entity->set(
            $field,
            $user->get('user_id')->getValue()
        );

        return $entity;
    }
}
