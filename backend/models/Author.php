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

    public function getById(int $id): ?array {
        $sql = "SELECT * FROM authors WHERE author_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function create(string $name): bool {
        $sql = "INSERT INTO authors (author_name) VALUES (:name)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['name' => $name]);
        return $stmt->fetch();
    }

    public function delete(int $id): bool {
        $sql = "DELETE FROM authors WHERE author_id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}