<?php
session_start();

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('STORAGE_PATH', BASE_PATH . '/storage');
define('RESOURCES_PATH', BASE_PATH . '/resources');
define('MODULES_PATH', BASE_PATH . '/modules');

if (file_exists(BASE_PATH . '/install.lock')) {
    die('Installation already completed. Delete install.lock file to reinstall.');
}

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$step = max(1, min(5, $step));

require_once __DIR__ . '/steps/step' . $step . '.php';
