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
        $query = $_GET['q'] ?? $_GET['name'] ?? null;
        $isSearch = $query !== null && trim($query) !== '';
        if ($isSearch) {
            $authors = $this->authorModel->search(trim($query));
            if (empty($authors)) {
                $this->jsonResponse(['success' => false, 'data' => []]);
                return;
            }
            $this->jsonResponse(['success' => true, 'data' => $authors]);
        }

        $authors = $this->authorModel->getAll();
        $this->jsonResponse(['success' => true, 'data' => $authors]);
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
        $created = $this->authorModel->create($input['name']);
        if (!$created) {
            $this->jsonResponse(['error' => 'Could not create author'], 500);
            return;
        }
        $this->jsonResponse(['success' => true, 'data' => $created], 201);
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

    // PUT/PATCH /authors/{id}
    public function update(int $id): void {
        $input = $this->getJsonInput();
        if (empty($input['name'])) {
            $this->jsonResponse(['error' => 'Name is required'], 422);
            return;
        }
        $updated = $this->authorModel->update($id, $input['name']);
        if (!$updated) {
            $this->jsonResponse(['error' => 'Author not found or not updated'], 404);
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