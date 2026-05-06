<?php
// Контроллер для /books

namespace Controllers;

use Models\Book;
use Models\Author;

class BookController {
    private Book $bookModel;
    private Author $authorModel;

    public function __construct() {
        $this->bookModel = new Book();
        $this->authorModel = new Author();
    }

    // GET /books
    public function index(): void {
        // Search by title via ?q= or ?title=
        $query = $_GET['q'] ?? $_GET['title'] ?? null;
        $isSearch = $query !== null && trim($query) !== '';
        if ($isSearch) {
            $books = $this->bookModel->searchByTitle(trim($query));
            if (empty($books)) {
                $this->jsonResponse(['success' => false, 'data' => []]);
                return;
            }
            $this->jsonResponse(['success' => true, 'data' => $books]);
        }

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
        $created = $this->bookModel->create(
            $input['title'],
            (int)$input['author_id'],
            $input['book_cover_url'] ?? null,
            $input['series_name'] ?? null,
            $input['book_status'] ?? null,
            $input['last_text_update'] ?? null,
            $input['annotation'] ?? null,
            $input['table_of_contents'] ?? null,
            is_array($input['genres'] ?? null) ? $input['genres'] : []
        );
        if (!$created) {
            $this->jsonResponse(['error' => 'Could not create book'], 500);
            return;
        }
        $this->jsonResponse(['success' => true, 'data' => $created], 201);
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

    // PUT/PATCH /books/{id}
    public function update(int $id): void {
        $input = $this->getJsonInput();
        if (empty($input['title']) || empty($input['author_id'])) {
            $this->jsonResponse(['error' => 'title and author_id are required'], 422);
            return;
        }
        $author = $this->authorModel->getById((int)$input['author_id']);
        if (!$author) {
            $this->jsonResponse(['error' => 'Author not found'], 404);
            return;
        }
        $updated = $this->bookModel->update(
            $id,
            $input['title'],
            (int)$input['author_id'],
            $input['book_cover_url'] ?? null,
            $input['series_name'] ?? null,
            $input['book_status'] ?? null,
            $input['last_text_update'] ?? null,
            $input['annotation'] ?? null,
            $input['table_of_contents'] ?? null,
            is_array($input['genres'] ?? null) ? $input['genres'] : []
        );
        if (!$updated) {
            $this->jsonResponse(['error' => 'Book not found or not updated'], 404);
            return;
        }
        $this->jsonResponse(['success' => true, 'data' => $updated]);
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