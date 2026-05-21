<?php

namespace Models;

use Config\Database;
use PDO;

class Genres {
    private PDO $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function getAll(): array {
        $sql = "SELECT genre_id, genre_name FROM genres ORDER BY genre_id";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $sql = "SELECT genre_id, genre_name FROM genres WHERE genre_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function getAllBooksByGenreId(int $genreId): array {
        $sql = "SELECT books.*, authors.author_name as author_name,
                        COALESCE(json_agg(DISTINCT genres.genre_name) FILTER (WHERE genres.genre_name IS NOT NULL),'[]') AS genres
                FROM books
                JOIN authors ON books.author_id = authors.author_id
                JOIN books_genres ON books.book_id = books_genres.book_id
                JOIN genres ON books_genres.genre_id = genres.genre_id
                WHERE genres.genre_id = :genreId
                GROUP BY books.book_id, authors.author_name
                ORDER BY books.book_id
            ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['genreId' => $genreId]);
        $rows = $stmt->fetchAll();
        return array_map([$this, 'decodeGenresInRow'], $rows);
    }
}