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

$router = new Router();
$router->dispatch();