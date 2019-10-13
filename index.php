<?php

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    require_once(dirname(__FILE__) . '\\includes\\bootstrap.php');
} else {
    require_once(dirname(__FILE__) . '/includes/bootstrap.php');
}
