#!/usr/bin/env php
<?php

require_once('bootstrap');

(new BeAmado\OjsMigrator\Extension\EconomicaFixer)->run();
