<?php

namespace BeAmado\OjsMigrator\Entity;
use \BeAmado\OjsMigrator\Registry;

class SubmissionHistoryHandler extends EntityHandler
{
    protected function importEventLogSettings($data)
    {
        if (!$data->hasAttribute('settings'))
            return true;

        return $data->get('settings')->forEachValue(function($setting) {
            return $this->importEntity(
                $setting,
                'event_log_settings',
                array(
                    'event_log' => 'log_id',
                ),
                true
            );
        });
    }

    protected function importEventLog($data)
    {
        if (!$data->hasAttributes(array(
            'assoc_id', 
            'assoc_type',
        )))
            return false;
        
        return $this->importEntity(
            $data,
            'event_log',
            array(
                'users' => 'user_id',
                Registry::get('AssocHandler')->getAssocTableName(
                    $data->get('assoc_type')->getValue()
                ) => 'assoc_id',
            ),
            true
        ) && $this->importEventLogSettings($data);
    }

    protected function importEmailLog($data)
    {
        if (!$data->hasAttributes(array(
            'email_log_user',
            'assoc_id',
            'assoc_type',
        )))
            return false;

        $data->set(
            'sender_id',
            0
        );

        return $this->importEntity(
            $data,
            'email_log',
            array(
                Registry::get('AssocHandler')->getAssocTableName(
                    $data->get('assoc_type')->getValue()
                ) => 'assoc_id',
            ),
            true
        ) && $this->importEntity(
            $data->get('email_log_user'),
            'email_log_users',
            array(
                'email_log' => 'email_log_id',
                'users' => 'user_id',
            ),
            true
        );
    }

    protected function importEventLogs($history)
    {
        if (!$history->hasAttribute('event_logs'))
            return true;

        return $history->get('event_logs')->forEachValue(function($e) {
            return $this->importEventLog($e);
        });
    }

    protected function importEmailLogs($history)
    {
        if (!$history->hasAttribute('email_logs'))
            return true;

        return $history->get('email_logs')->forEachValue(function($e) {
            return $this->importEmailLog($e);
        });
    }

    public function importHistory($submission)
    {
        if (!$submission->hasAttribute('history'))
            return false;

        if (
            !$submission->get('history')->hasAttribute('email_logs') &&
            !$submission->get('history')->hasAttribute('event_logs')
        )
            return false;

        return $this->importEventLogs($submission->get('history')) &&
            $this->importEmailLogs($submission->get('history'));
    }

    protected function smHr()
    {
        return Registry::get('SubmissionHandler');
    }

    protected function getEventLogs($submissionId)
    {
        if (!\is_numeric($submissionId))
            return;
        
        $eventLogs = $this->smHr()->getDAO('search_objects')->read(array(
            'assoc_id' => $submissionId,
            'assoc_type' => Registry::get('AssocHandler')
                                    ->getAssocType('submission'),
        ));


    }
}
