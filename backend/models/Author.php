<?php

namespace Models;

use Config\Database;
use PDO;

class Author {
    private PDO $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function getAll(): array {
        $sql = "SELECT * FROM authors ORDER BY author_id";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function search(string $term): array {
        $sql = "SELECT * FROM authors WHERE author_name ILIKE :q ORDER BY author_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['q' => '%' . $term . '%']);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $sql = "SELECT * FROM authors WHERE author_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(string $name): ?array {
        $sql = "INSERT INTO authors (author_name) VALUES (:name) RETURNING *";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['name' => $name]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function delete(int $id): bool {
        $sql = "DELETE FROM authors WHERE author_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function update(int $id, string $name): ?array {
        $sql = "UPDATE authors SET author_name = :name WHERE author_id = :id RETURNING *";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id, 'name' => $name]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}