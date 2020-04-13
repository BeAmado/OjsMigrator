#!/usr/bin/env php
<?php

namespace BeAmado\OjsMigrator;

require_once('smoke-bootstrap.php');
(new Application(array(
    'OjsDir' => Registry::get('OjsDir'),
    'clearRegistry' => false,
)))->run();
require_once('smoke-clear.php');
