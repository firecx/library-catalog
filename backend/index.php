<?php
// Автозагрузка классов по PSR-4 (простая)
spl_autoload_register(function ($class) {
    // Префикс пространства имён 'Config', 'Models', 'Controllers', 'Router'
    $prefix = '';
    $base_dir = __DIR__ . '/';
    // Проверяем соответствие
    $file = $base_dir . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Инициализация роутера
use Router\Router;

$router = new Router();
$router->dispatch();