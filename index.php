<?php
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('STORAGE_PATH', BASE_PATH . '/storage');
define('RESOURCES_PATH', BASE_PATH . '/resources');
define('MODULES_PATH', BASE_PATH . '/modules');

if (file_exists(BASE_PATH . '/install.lock')) {
    if (file_exists(BASE_PATH . '/config.php')) {
        require_once BASE_PATH . '/config.php';
    }

    require_once APP_PATH . '/core/App.php';
    require_once APP_PATH . '/core/Router.php';
    require_once APP_PATH . '/core/Controller.php';
    require_once APP_PATH . '/core/Model.php';
    require_once APP_PATH . '/core/Database.php';
    require_once APP_PATH . '/core/Request.php';
    require_once APP_PATH . '/core/Response.php';
    require_once APP_PATH . '/core/Session.php';
    require_once APP_PATH . '/core/View.php';
    require_once APP_PATH . '/helpers/functions.php';

    $app = new App();
    $app->run();
} else {
    header('Location: /installer/');
    exit;
}
