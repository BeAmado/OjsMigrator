<?php

namespace BeAmado\OjsMigrator;

class IssueMock extends EntityMock
{
    use JournalFiller;
    use SectionFiller;

    public function __construct($name = null)
    {
        parent::__construct('issues');
    }

    protected function fill($issue)
    {
        $this->fillJournalId($issue->get('custom_order'));

        $issue->get('custom_section_orders')->forEachValue(function($cso) {
            $this->fillSectionId($cso);
        });

        $this->fillJournalId($issue);

        return $issue;
    }

    public function getIssue($name)
    {
        return Registry::get('IssueHandler')->create(
            $this->fill($this->get($name))
        );
    }

    public function getRWC2011Issue()
    {
        return $this->getIssue('2011');
    }

    public function getRWC2015Issue()
    {
        return $this->getIssue('2015');
    }
}
