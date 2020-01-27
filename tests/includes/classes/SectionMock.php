<?php

namespace BeAmado\OjsMigrator;

class SectionMock extends EntityMock
{
    use UserFiller;
    use JournalFiller;
    use ReviewFormFiller;

    public function __construct($name = null)
    {
        parent::__construct('sections');
    }

    protected function fill($section)
    {
        $section->get('editors')->forEachValue(function($e) {
            $this->fillJournalId($e);
            $this->fillUserId($e);
        });

        $this->fillReviewFormId($section);
        $this->fillJournalId($section);

        return $section;
    }

    public function getSection($name)
    {
        return $this->fill($this->get($name));
    }

    public function getSportsSection()
    {
        return $this->fill($this->get('sports'));
    }

}
