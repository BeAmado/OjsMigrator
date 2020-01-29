<?php

namespace BeAmado\OjsMigrator;

trait SectionFiller
{
    /**
     * Fills the entity section_id field with the proper id.
     *
     * @param \BeAmado\OjsMigrator\MyObject
     * @param string $field
     * @return \BeAmado\OjsMigrator\MyObject 
     */
    public function fillSectionId($entity, $field = 'section_id')
    {
        if (
            !\is_a($entity, \BeAmado\OjsMigrator\MyObject::class) ||
            !$entity->hasAttribute($field)
        )
            return false;

        $section = (new SectionMock())->getSection(\str_replace(
            array('[', '_section', '_id', ']'),
            '', 
            $entity->get($field)->getValue()
        ));

        if (
            !\is_a($section, \BeAmado\OjsMigrator\MyObject::class) ||
            !$section->hasAttribute('section_id')
        )
            return false;

        $entity->set(
            $field,
            $section->get('section_id')->getValue()
        );

        return $entity;
    }
}
