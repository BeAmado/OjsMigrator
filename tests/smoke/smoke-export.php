<?php

(new \BeAmado\OjsMigrator\Test\FixtureHandler())->createSeveral(array(
    'journals' => array(
        'test_journal',
    ),
    'users' => array(
        'ironman',
        'hulk',
        'batman',
        'thor',
        'greenlantern',
    ),
    'review_forms' => array(
        'first',
        'second',
    ),
    'sections' => array(
        'sports',
        'sciences',
    ),
    'issues' => array(
        '2011',
        '2015',
    ),
    'groups' => array(
        'forwards',
        'backs',
    ),
    'announcements' => array(
        'welcome',
        'inscriptions',
    ),
    'submissions' => array(
        'rwc2011',
        'rwc2015',
        'trc2015',
    ),
    'keywords' => array(
        'rwc2011',
        'rwc2015',
        'trc2015',
    ),
), true);
