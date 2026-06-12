<?php

namespace App\Services;

use App\Config\Database;
use PDO;
use PDOException;

class BookService {
    private PDO $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function getAllBooks(): array {
        try {
            $sql = "

            ";

            $stmt = $this->db->query($sql);

            $authors = $stmt->fetchAll();

            return  $authors ?: [];
        } catch (PDOException $e) {
            throw new PDOException('Ошибка выполнения запроса (getAllBooks): ' . $e->getMessage());
        }
    }

    public function getBookById(int $id): array {
        try {
            $sql = "

            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);

            $author = $stmt->fetch();

            return $author ?: [];
        } catch (PDOException $e) {
            throw new PDOException('Ошибка выполнения запроса (getBookById): ' . $e->getMessage());
        }
    }

    public function getBookByName(string $name): array {
        try {
            $sql = "

            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':name' => $name]);

            $author = $stmt->fetch();

            return $author ?: [];
        } catch (PDOException $e) {
            throw new PDOException('Ошибка выполнения запроса (getBookByName): ' . $e->getMessage());
        }
    }

    public function searchBooks(string $query): array {
        try {
            $sql = "

            ";

            $stmt = $this->db->prepare($sql);
            $searchTerm = "%{$query}%";
            $stmt->bindValue(':query', $searchTerm, PDO::PARAM_STR);
            $stmt->execute();

            $authors = $stmt->fetchAll();

            return $authors ?: [];
        } catch (PDOException $e) {
            throw new PDOException('Ошибка выполнения запроса (searchBooks): ' . $e->getMessage());
        }
    }
}