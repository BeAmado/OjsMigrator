<?php

$fh = new \BeAmado\OjsMigrator\Test\FixtureHandler();
$entities = array(
    'journals' => array(
        'test_journal',
    ),
    'users' => array(
        'ironman',
        'batman',
        'thor',
        'greenlantern',
        'stewart',
        'hawkeye',
        'hulk',
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
    'submissions' => array(
        'rwc2011',
        'rwc2015',
        'trc2015',
    ),
    'announcements' => array(
        'welcome',
        'inscriptions',
    ),
    'groups' => array(
        'backs',
        'forwards',
    ),
);
$fh->createTablesForEntities(array_keys($entities));
$fh->createSingle('journals', 'test_journal');
$fh->createEntities($entities); // creates them in the entities directory
