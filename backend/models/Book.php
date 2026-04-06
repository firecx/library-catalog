<?php

namespace Models;

use Config\Database;
use PDO;

class Book {
    private PDO $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function getAll(): array {
        $sql = "SELECT books.*, authors.name as author_name 
                FROM books 
                JOIN authors ON books.author_id = authors.id 
                ORDER BY books.id
            ";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getById(int $id): array {
        $sql = "SELECT books.*, authors.name as author_name 
                FROM books 
                JOIN authors ON books.author_id = authors.id 
                WHERE books.id = :id
            ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $book = $stmt->fetch();
        return $book ?: null;
    }

    public function create(string $title, int $authorId, ?int $year = null): bool {
        $sql = "INSERT INTO books (title, author_id, year) 
                VALUES (:title, :author_id, :year) 
                RETURNING *
        ";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'title' => $title,
            'author_id' => $authorId,
            'year' => $year
        ]);
    }

    public function delete(int $id): bool {
        $sql = "DELETE FROM books WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function getByAuthor(int $authorId): array {
        $sql = "SELECT * FROM books WHERE author_id = :author_id ORDER BY id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['author_id' => $authorId]);
        return $stmt->fetchAll();
    }

}