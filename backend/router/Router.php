<?php

namespace Router;

use controllers\AuthorController;
use controllers\BookController;

class Router {
    private string $method;
    private string $path;
    private array $pathParts;

    public function __construct() {
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->path = $_SERVER['PATH_INFO'] ?? '/';
        $this->pathParts = explode('/', trim($this->path, '/'));
    }

    public function dispatch(): void {
        // Если первый сегмент пустой — корень
        $resource = $this->pathParts[0] ?? '';
        // Второй сегмент может быть ID или подресурс
        $id = isset($this->pathParts[1]) && is_numeric($this->pathParts[1]) 
            ? (int)$this->pathParts[1] 
            : null;
        $subresource = isset($this->pathParts[1]) && !is_numeric($this->pathParts[1])
            ? $this->pathParts[1]
            : ($this->pathParts[2] ?? null);

        // Маршрутизация
        switch ($resource) {
            case 'authors':
                $controller = new AuthorController();
                $this->handleAuthor($controller, $id, $subresource);
                break;
            case 'books':
                $controller = new BookController();
                $this->handleBook($controller, $id, $subresource);
                break;
            default:
                $this->jsonResponse(['error' => 'Not Found'], 404);
        }
    }

    private function handleAuthor(AuthorController $controller, ?int $id, ?string $subresource): void {
        if ($id === null) {
            // Коллекция
            if ($this->method === 'GET') {
                $controller->index();
            } elseif ($this->method === 'POST') {
                $controller->store();
            } else {
                $this->methodNotAllowed();
            }
        } else {
            // Конкретный автор
            if ($this->method === 'GET') {
                $controller->show($id);
            } elseif ($this->method === 'DELETE') {
                $controller->destroy($id);
            } else {
                $this->methodNotAllowed();
            }
        }
    }

    private function handleBook(BookController $controller, ?int $id, ?string $subresource): void {
        // Дополнительный параметр: GET /books?author_id=1 обрабатывается через query string
        // Но для RESTful можно и через /books/author/1
        if ($subresource === 'author' && isset($this->pathParts[2]) && is_numeric($this->pathParts[2])) {
            if ($this->method === 'GET') {
                $controller->indexByAuthor((int)$this->pathParts[2]);
            } else {
                $this->methodNotAllowed();
            }
            return;
        }

        if ($id === null) {
            if ($this->method === 'GET') {
                // Проверяем query-параметр author_id
                if (isset($_GET['author_id']) && is_numeric($_GET['author_id'])) {
                    $controller->indexByAuthor((int)$_GET['author_id']);
                } else {
                    $controller->index();
                }
            } elseif ($this->method === 'POST') {
                $controller->store();
            } else {
                $this->methodNotAllowed();
            }
        } else {
            if ($this->method === 'GET') {
                $controller->show($id);
            } elseif ($this->method === 'DELETE') {
                $controller->destroy($id);
            } else {
                $this->methodNotAllowed();
            }
        }
    }

    private function methodNotAllowed(): void {
        $this->jsonResponse(['error' => 'Method Not Allowed'], 405);
    }

    private function jsonResponse($data, int $code): void {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}