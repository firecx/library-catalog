<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Router;
use App\Request;
use App\Controllers\AuthorController;

$router = new Router();

$router->registerController(AuthorController::class);

$request = new Request();
$router->dispatch($request);