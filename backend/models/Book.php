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
        $sql = "SELECT books.*, authors.author_name as author_name,
                        COALESCE(json_agg(DISTINCT genres.genre_name) FILTER (WHERE genres.genre_name IS NOT NULL),'[]') AS genres
                FROM books
                JOIN authors ON books.author_id = authors.author_id
                LEFT JOIN books_genres ON books.book_id = books_genres.book_id
                LEFT JOIN genres ON books_genres.genre_id = genres.genre_id
                GROUP BY books.book_id, authors.author_name
                ORDER BY books.book_id
            ";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll();
        return array_map([$this, 'decodeGenresInRow'], $rows);
    }

    public function searchByTitle(string $term): array {
        $sql = "SELECT books.*, authors.author_name as author_name,
                        COALESCE(json_agg(DISTINCT genres.genre_name) FILTER (WHERE genres.genre_name IS NOT NULL),'[]') AS genres
                FROM books
                JOIN authors ON books.author_id = authors.author_id
                LEFT JOIN books_genres ON books.book_id = books_genres.book_id
                LEFT JOIN genres ON books_genres.genre_id = genres.genre_id
                WHERE books.book_title ILIKE :q
                GROUP BY books.book_id, authors.author_name
                ORDER BY books.book_id
            ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['q' => '%' . $term . '%']);
        $rows = $stmt->fetchAll();
        return array_map([$this, 'decodeGenresInRow'], $rows);
    }

    public function getById(int $id): ?array {
        $sql = "SELECT books.*, authors.author_name as author_name,
                        COALESCE(json_agg(DISTINCT genres.genre_name) FILTER (WHERE genres.genre_name IS NOT NULL),'[]') AS genres
                FROM books
                JOIN authors ON books.author_id = authors.author_id
                LEFT JOIN books_genres ON books.book_id = books_genres.book_id
                LEFT JOIN genres ON books_genres.genre_id = genres.genre_id
                WHERE books.book_id = :id
                GROUP BY books.book_id, authors.author_name
            ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $book = $stmt->fetch();
        return $book ? $this->decodeGenresInRow($book) : null;
    }

    public function create(string $title, int $authorId, ?string $coverUrl = null, ?string $seriesName = null, ?string $status = null, ?string $lastTextUpdate = null, ?string $annotation = null, ?string $tableOfContents = null, array $genres = []): ?array {
        $sql = "INSERT INTO books (book_title, author_id, book_cover_url, series_name, book_status, last_text_update, annotation, table_of_contents)
                VALUES (:title, :author_id, :cover_url, :series_name, :status, :last_text_update, :annotation, :table_of_contents)
                RETURNING *
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'title' => $title,
            'author_id' => $authorId,
            'cover_url' => $coverUrl,
            'series_name' => $seriesName,
            'status' => $status ?? 'in_progress',
            'last_text_update' => $lastTextUpdate,
            'annotation' => $annotation,
            'table_of_contents' => $tableOfContents,
        ]);
        $row = $stmt->fetch();
        if ($row && !empty($genres)) {
            $genreIds = $this->upsertGenres($genres);
            $this->setBookGenres((int)$row['book_id'], $genreIds);
            // reload to include genres
            return $this->getById((int)$row['book_id']);
        }
        return $row ? $this->decodeGenresInRow($row) : null;
    }

    public function delete(int $id): bool {
        $sql = "DELETE FROM books WHERE book_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function getByAuthor(int $authorId): array {
        $sql = "SELECT books.*, authors.author_name as author_name,
                        COALESCE(json_agg(DISTINCT genres.genre_name) FILTER (WHERE genres.genre_name IS NOT NULL),'[]') AS genres
                FROM books
                JOIN authors ON books.author_id = authors.author_id
                LEFT JOIN books_genres ON books.book_id = books_genres.book_id
                LEFT JOIN genres ON books_genres.genre_id = genres.genre_id
                WHERE books.author_id = :author_id
                GROUP BY books.book_id, authors.author_name
                ORDER BY books.book_id
            ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['author_id' => $authorId]);
        $rows = $stmt->fetchAll();
        return array_map([$this, 'decodeGenresInRow'], $rows);
    }

    public function update(int $id, string $title, int $authorId, ?string $coverUrl = null, ?string $seriesName = null, ?string $status = null, ?string $lastTextUpdate = null, ?string $annotation = null, ?string $tableOfContents = null, array $genres = []): ?array {
        $sql = "UPDATE books SET book_title = :title, author_id = :author_id, book_cover_url = :cover_url, series_name = :series_name, book_status = :status, last_text_update = :last_text_update, annotation = :annotation, table_of_contents = :table_of_contents WHERE book_id = :id RETURNING *";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id, 'title' => $title, 'author_id' => $authorId, 'cover_url' => $coverUrl, 'series_name' => $seriesName, 'status' => $status ?? 'in_progress', 'last_text_update' => $lastTextUpdate, 'annotation' => $annotation, 'table_of_contents' => $tableOfContents]);
        $row = $stmt->fetch();
        if ($row) {
            if (!empty($genres)) {
                $genreIds = $this->upsertGenres($genres);
                $this->setBookGenres((int)$row['book_id'], $genreIds);
            }
            return $this->getById((int)$row['book_id']);
        }
        return null;
    }

    private function upsertGenres(array $genres): array {
        $ids = [];
        $select = $this->db->prepare('SELECT genre_id FROM genres WHERE genre_name = :name');
        $insert = $this->db->prepare('INSERT INTO genres (genre_name) VALUES (:name) RETURNING genre_id');
        foreach ($genres as $g) {
            $name = trim($g);
            if ($name === '') continue;
            $select->execute(['name' => $name]);
            $row = $select->fetch();
            if ($row) {
                $ids[] = (int)$row['genre_id'];
                continue;
            }
            $insert->execute(['name' => $name]);
            $r = $insert->fetch();
            if ($r) $ids[] = (int)$r['genre_id'];
        }
        return $ids;
    }

    private function setBookGenres(int $bookId, array $genreIds): void {
        $del = $this->db->prepare('DELETE FROM books_genres WHERE book_id = :book_id');
        $del->execute(['book_id' => $bookId]);
        $ins = $this->db->prepare('INSERT INTO books_genres (book_id, genre_id) VALUES (:book_id, :genre_id)');
        foreach ($genreIds as $gid) {
            $ins->execute(['book_id' => $bookId, 'genre_id' => $gid]);
        }
    }

    private function decodeGenresInRow(array $row): array {
        if (isset($row['genres'])) {
            // genres comes as JSON text (Postgres json_agg) — ensure PHP array
            if (is_string($row['genres'])) {
                $decoded = json_decode($row['genres'], true);
                $row['genres'] = $decoded === null ? [] : $decoded;
            }
        } else {
            $row['genres'] = [];
        }
        return $row;
    }

}