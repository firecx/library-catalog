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
        return;
    }
    // На файловых системах с чувствительностью к регистру (Linux) наши каталоги могут быть
    // в нижнем регистре. Попробуем альтернативный путь со строчными сегментами.
    $alt = $base_dir . strtolower(str_replace('\\', '/', $class)) . '.php';
    if (file_exists($alt)) {
        require $alt;
        return;
    }
});

// Инициализация роутера
use router\Router;

// Send CORS headers for API requests. Adjust origin as needed for production.
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests quickly
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$router = new Router();
$router->dispatch();