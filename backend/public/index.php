<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Router;
use App\Request;
use App\Controllers\AuthorController;
use App\JsonResponse;

try {
    $router = new Router();
    $router->registerController(AuthorController::class);
    
    $request = new Request();
    $router->dispatch($request);
    
} catch (Exception $e) {
    JsonResponse::error($e->getMessage());
}