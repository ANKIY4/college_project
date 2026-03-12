<?php

if (!defined('PROJECT_ROOT')) {
    define('PROJECT_ROOT', dirname(__DIR__));
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$GLOBALS['config'] = require __DIR__ . '/config.php';

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/validation.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/events.php';

date_default_timezone_set(config('app.timezone', 'Asia/Kathmandu'));
