<?php

namespace App\Services;

use App\Config\Database;
use PDO;
use PDOException;

class AuthorService {
    private PDO $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function getAllAuthors(): array {
        try {
            $sql = "
                SELECT *
                FROM authors
                ORDER BY author_id
            ";

            $stmt = $this->db->query($sql);

            $authors = $stmt->fetchAll();

            return  $authors ?: [];
        } catch (PDOException $e) {
            throw new PDOException('Ошибка выполнения запроса (getAllAuthors): ' . $e->getMessage());
        }
    }

    public function getAuthorById(int $id): array {
        try {
            $sql = "
                SELECT *
                FROM authors
                WHERE author_id = :id
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);

            $author = $stmt->fetch();

            return $author ?: [];
        } catch (PDOException $e) {
            throw new PDOException('Ошибка выполнения запроса (getAuthorById): ' . $e->getMessage());
        }
    }

    public function getAuthorByName(string $name): array {
        try {
            $sql = "
                SELECT *
                FROM authors
                WHERE author_name = :name
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':name' => $name]);

            $author = $stmt->fetch();

            return $author ?: [];
        } catch (PDOException $e) {
            throw new PDOException('Ошибка выполнения запроса (getAuthorByName): ' . $e->getMessage());
        }
    }

    public function searchAuthors(string $query): array {
        try {
            $sql = "
                SELECT *
                FROM authors
                WHERE author_name ILIKE :query
                ORDER BY author_id
            ";

            $stmt = $this->db->prepare($sql);
            $searchTerm = "%{$query}%";
            $stmt->bindValue(':query', $searchTerm, PDO::PARAM_STR);
            $stmt->execute();

            $authors = $stmt->fetchAll();

            return $authors ?: [];
        } catch (PDOException $e) {
            throw new PDOException('Ошибка выполнения запроса (searchAuthors): ' . $e->getMessage());
        }
    }
}