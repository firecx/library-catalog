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
                SELECT 
                    b.book_id,
                    b.book_title,
                    b.book_cover_url,
                    b.annotation,
                    b.table_of_contents,
                    b.author_id,
                    a.author_name,
                    COALESCE(
                        json_agg(
                            DISTINCT jsonb_build_object(
                                'genre_id', g.genre_id,
                                'genre_name', g.genre_name
                            )
                        ) FILTER (WHERE g.genre_id IS NOT NULL),
                        '[]'::json
                    ) AS genres
                FROM books b
                JOIN authors a ON b.author_id = a.author_id
                LEFT JOIN books_genres bg ON b.book_id = bg.book_id
                LEFT JOIN genres g ON bg.genre_id = g.genre_id
                GROUP BY b.book_id, a.author_id
                ORDER BY b.book_id
            ";

            $stmt = $this->db->query($sql);

            $authors = $stmt->fetchAll();

            return  $authors ?: [];
        } catch (PDOException $e) {
            throw new PDOException('Ошибка выполнения запроса (getAllBooks): ' . $e->getMessage());
        }
    }

    /**
     * @param int $offset - с какой позиции начинать (начиная с 0)
     * @param int $limit - сколько книг получить
     * @return array
     */
    public function getBooksPaginated(int $offset, int $limit): array {
        try {
            $sql = "
                SELECT 
                    b.book_id,
                    b.book_title,
                    b.book_cover_url,
                    b.annotation,
                    b.table_of_contents,
                    b.author_id,
                    a.author_name,
                    COALESCE(
                        json_agg(
                            DISTINCT jsonb_build_object(
                                'genre_id', g.genre_id,
                                'genre_name', g.genre_name
                            )
                        ) FILTER (WHERE g.genre_id IS NOT NULL),
                        '[]'::json
                    ) AS genres
                FROM books b
                JOIN authors a ON b.author_id = a.author_id
                LEFT JOIN books_genres bg ON b.book_id = bg.book_id
                LEFT JOIN genres g ON bg.genre_id = g.genre_id
                GROUP BY b.book_id, a.author_id
                ORDER BY b.book_id
                LIMIT :limit OFFSET :offset
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $books = $stmt->fetchAll();

            return $books ?: [];
        } catch (PDOException $e) {
            throw new PDOException('Ошибка выполнения запроса (getBooksPaginated): ' . $e->getMessage());
        }
    }

    public function getBookById(int $id): array {
        try {
            $sql = "
                SELECT 
                    b.book_id,
                    b.book_title,
                    b.book_cover_url,
                    b.annotation,
                    b.table_of_contents,
                    b.author_id,
                    a.author_name,
                    COALESCE(
                        json_agg(
                            DISTINCT jsonb_build_object(
                                'genre_id', g.genre_id,
                                'genre_name', g.genre_name
                            )
                        ) FILTER (WHERE g.genre_id IS NOT NULL),
                        '[]'::json
                    ) AS genres
                FROM books b
                JOIN authors a ON b.author_id = a.author_id
                LEFT JOIN books_genres bg ON b.book_id = bg.book_id
                LEFT JOIN genres g ON bg.genre_id = g.genre_id
                WHERE b.book_id = :id
                GROUP BY b.book_id, a.author_id
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
                SELECT 
                    b.book_id,
                    b.book_title,
                    b.book_cover_url,
                    b.annotation,
                    b.table_of_contents,
                    b.author_id,
                    a.author_name,
                    COALESCE(
                        json_agg(
                            DISTINCT jsonb_build_object(
                                'genre_id', g.genre_id,
                                'genre_name', g.genre_name
                            )
                        ) FILTER (WHERE g.genre_id IS NOT NULL),
                        '[]'::json
                    ) AS genres
                FROM books b
                JOIN authors a ON b.author_id = a.author_id
                LEFT JOIN books_genres bg ON b.book_id = bg.book_id
                LEFT JOIN genres g ON bg.genre_id = g.genre_id
                WHERE LOWER(b.book_title) = LOWER(:name)
                GROUP BY b.book_id, a.author_id
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
                SELECT 
                    b.book_id,
                    b.book_title,
                    b.book_cover_url,
                    b.annotation,
                    b.table_of_contents,
                    b.author_id,
                    a.author_name,
                    COALESCE(
                        json_agg(
                            DISTINCT jsonb_build_object(
                                'genre_id', g.genre_id,
                                'genre_name', g.genre_name
                            )
                        ) FILTER (WHERE g.genre_id IS NOT NULL),
                        '[]'::json
                    ) AS genres
                FROM books b
                JOIN authors a ON b.author_id = a.author_id
                LEFT JOIN books_genres bg ON b.book_id = bg.book_id
                LEFT JOIN genres g ON bg.genre_id = g.genre_id
                WHERE 
                    LOWER(b.book_title) LIKE LOWER(:query)
                    OR LOWER(COALESCE(b.annotation, '')) LIKE LOWER(:query)
                    OR LOWER(a.author_name) LIKE LOWER(:query)
                GROUP BY b.book_id, a.author_id
                ORDER BY 
                    CASE 
                        WHEN LOWER(b.book_title) LIKE LOWER(:prefix) THEN 1
                        WHEN LOWER(a.author_name) LIKE LOWER(:prefix) THEN 2
                        ELSE 3
                    END,
                    b.book_title
                LIMIT 50
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