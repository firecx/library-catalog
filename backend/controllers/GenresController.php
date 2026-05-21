<?php

namespace Controllers;

use Models\Genre;

class GenresController {
    private Genre $genreModel;

    public function __construct() {
        $this->genreModel = new Genre();
    }

    // GET /genres
    public function index(): void {
        $genres = $this->genreModel->getAll();
        $this->jsonResponse(['success' => true, 'data' => $genres]);
    }

    public function show(int $id): void {
        $genre = $this->genreModel->getById($id);
        if (!$genre) {
            $this->jsonResponse(['error' => 'Genre not found'], 404);
            return;
        }
        $this->jsonResponse(['success' => true, 'data' => $genre]);
    }

    public function showBooksByGenre(int $id): void {
        $books = $this->genreModel->getAllBooksByGenreId($id);
        if (empty($books)) {
            $this->jsonResponse(['error' => 'No books found for this genre'], 404);
            return;
        }
        $this->jsonResponse(['success' => true, 'data' => $books]);
    }

    private function jsonResponse(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}