<?php
// Контроллер для /books

namespace Controllers;

use models\Book;
use models\Author;

class BookController {
    private Book $bookModel;
    private Author $authorModel;

    public function __construct() {
        $this->bookModel = new Book();
        $this->authorModel = new Author();
    }

    // GET /books
    public function index(): void {
        $books = $this->bookModel->getAll();
        $this->jsonResponse(['success' => true, 'data' => $books]);
    }

    // GET /books/{id}
    public function show(int $id): void {
        $book = $this->bookModel->getById($id);
        if (!$book) {
            $this->jsonResponse(['error' => 'Book not found'], 404);
            return;
        }
        $this->jsonResponse(['success' => true, 'data' => $book]);
    }

    // POST /books
    public function store(): void {
        $input = $this->getJsonInput();
        if (empty($input['title']) || empty($input['author_id'])) {
            $this->jsonResponse(['error' => 'title and author_id are required'], 422);
            return;
        }
        // Проверяем, существует ли автор
        $author = $this->authorModel->getById($input['author_id']);
        if (!$author) {
            $this->jsonResponse(['error' => 'Author not found'], 404);
            return;
        }
        $book = $this->bookModel->create(
            $input['title'],
            $input['author_id'],
            $input['year'] ?? null
        );
        $this->jsonResponse(['success' => true, 'data' => $book], 201);
    }

    // DELETE /books/{id}
    public function destroy(int $id): void {
        $deleted = $this->bookModel->delete($id);
        if (!$deleted) {
            $this->jsonResponse(['error' => 'Book not found'], 404);
            return;
        }
        $this->jsonResponse(['success' => true, 'message' => 'Book deleted']);
    }

    // Доп. маршрут: GET /books?author_id=1
    public function indexByAuthor(int $authorId): void {
        $books = $this->bookModel->getByAuthor($authorId);
        $this->jsonResponse(['success' => true, 'data' => $books]);
    }

    private function getJsonInput(): array {
        $json = file_get_contents('php://input');
        return json_decode($json, true) ?? [];
    }

    private function jsonResponse($data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}