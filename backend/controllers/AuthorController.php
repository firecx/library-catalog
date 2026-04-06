<?php

namespace Controllers;

use Models\Author;

class AuthorController {
    private Author $authorModel;

    public function __construct() {
        $this->authorModel = new Author();
    }

    // GET /authors — список всех авторов
    public function index(): void {
        $authors = $this->authorModel->getAll();
        $this->jsonResponse($authors);
    }

    // GET /authors/{id} — один автор
    public function show(int $id): void {
        $author = $this->authorModel->getById($id);
        if (!$author) {
            $this->jsonResponse(['error' => 'Author not found'], 404);
            return;
        }
        $this->jsonResponse(['success' => true, 'data' => $author]);
    }

    // POST /authors — создать автора
    public function store(): void {
        $input = $this->getJsonInput();
        if (empty($input['name'])) {
            $this->jsonResponse(['error' => 'Name is required'], 422);
            return;
        }
        $author = $this->authorModel->create($input['name']?? null);
        $this->jsonResponse(['success' => true, 'data' => $author], 201);
    }

    // DELETE /authors/{id} — удалить автора
    public function destroy(int $id): void {
        $deleted = $this->authorModel->delete($id);
        if (!$deleted) {
            $this->jsonResponse(['error' => 'Author not found'], 404);
            return;
        }
        $this->jsonResponse(['success' => true, 'message' => 'Author deleted']);
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