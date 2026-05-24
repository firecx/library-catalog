<?php

require_once 'Router.php';
require_once 'Request.php';
require_once 'JsonResponse.php';

spl_autoload_register(function ($class) {
    $controllers_dir = __DIR__ . '/controllers/';
    $file = $controllers_dir . $class . '.php';
    
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    return false;
});

$router = new Router();

$router->registerController(AuthorController::class);

$request = new Request();

$router->dispatch($request);