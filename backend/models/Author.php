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
        $stmt = $this->db->query('SELECT * FROM authors ORDER BY id');
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare('SELECT * FROM authors WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function create(string $name): bool {
        $stmt = $this->db->prepare("INSERT INTO authors (name)
                                    VALUES (:name)");
        $stmt->execute(['name' => $name]);
        return $stmt->fetch();
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM authors WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}