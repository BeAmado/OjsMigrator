<?php

namespace BeAmado\OjsMigrator;

trait ReviewFormFiller
{
    /**
     * Fills the entity review_form_id field with the proper id.
     *
     * @param \BeAmado\OjsMigrator\MyObject
     * @param string $field
     * @return \BeAmado\OjsMigrator\MyObject 
     */
    public function fillReviewFormId($entity, $field = 'review_form_id')
    {
        if (
            !\is_a($entity, \BeAmado\OjsMigrator\MyObject::class) ||
            !$entity->hasAttribute($field)
        )
            return false;

        $reviewForm = (new ReviewFormMock())->getReviewForm(\str_replace(
            array('[', '_review_form', '_id', ']'),
            '', 
            $entity->get($field)->getValue()
        ));

        if (
            !\is_a($reviewForm, \BeAmado\OjsMigrator\MyObject::class) ||
            !$reviewForm->hasAttribute('review_form_id')
        )
            return false;

        $entity->set(
            $field,
            $reviewForm->get('review_form_id')->getValue()
        );

        return $entity;
    }
}
