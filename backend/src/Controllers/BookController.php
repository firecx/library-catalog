<?php

namespace App\Controllers;

use App\Route;
use App\Request;
use App\JsonResponse;
use App\Services\BookService;
use PDOException;

#[Route('/api/books')]
class BookController {

    private BookService $bookService;

    public function __construct() {
        $this->bookService = new BookService();
    }

    #[Route('', methods: ['GET'])]
    public function getAllBooks(Request $request): JsonResponse {
        
        try {
            $books = $this->bookService->getAllBooks();

            return JsonResponse::success($books);
        } catch (PDOException $e) {
            return JsonResponse::error("Ошибка выполнения запроса: " . $e->getMessage());
        } catch (\Exception $e) {
            return JsonResponse::error($e->getMessage(), 500);
        }
    }

    #[Route('/id/{id}', methods: ['GET'])]
    public function getBook(Request $request, int|string $id): JsonResponse {
        if (!is_numeric($id)) {
            return JsonResponse::error('Invalid author ID', 400);
        }
        $id = (int) $id;

        try {
            $book = $this->bookService->getBookById($id);

            if (!$book) {
                return JsonResponse::error('Book not found', 404);
            }

            return JsonResponse::success($book);
        } catch (PDOException $e) {
            return JsonResponse::error("Ошибка выполнения запроса: " . $e->getMessage());
        } catch (\Exception $e) {
            return JsonResponse::error($e->getMessage(), 500);
        }
    }

    #[Route('/search', methods: ['GET'])]
    public function search(Request $request): JsonResponse {
        try {
            $query = $request->query('q', '');
            
            if (empty($query)) {
                return JsonResponse::error('Search query (q) is required', 400);
            }
            
            $books = $this->bookService->searchBooks($query);
                       
            return JsonResponse::success($books);
            
        } catch (PDOException $e) {
            return JsonResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return JsonResponse::error($e->getMessage(), 500);
        }
    }
}