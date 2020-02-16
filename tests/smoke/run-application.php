#!/usr/bin/env php
<?php

namespace BeAmado\OjsMigrator;

require_once('smoke-bootstrap.php');
(new Application())->run(Registry::get('OjsDir'));
require_once('smoke-clear.php');
