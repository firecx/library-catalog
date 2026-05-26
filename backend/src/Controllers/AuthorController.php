<?php

namespace App\Controllers;

use App\Route;
use App\Request;
use App\JsonResponse;
use App\Services\AuthorService;
use PDOException;

#[Route('/api/authors')]
class AuthorController {

    private AuthorService $authorService;

    public function __construct() {
        $this->authorService = new AuthorService();
    }

    #[Route('', methods: ['GET'])]
    public function getAllAuthors(Request $request): JsonResponse {
        
        try {
            $authors = $this->authorService->getAllAuthors();

            return JsonResponse::success($authors);
        } catch (PDOException $e) {
            return JsonResponse::error("Ошибка выполнения запроса: " . $e->getMessage());
        } catch (\Exception $e) {
            return JsonResponse::error($e->getMessage(), 500);
        }
    }

    #[Route('/id/{id}', methods: ['GET'])]
    public function getAuthor(Request $request, int|string $id): JsonResponse {
        if (!is_numeric($id)) {
            return JsonResponse::error('Invalid author ID', 400);
        }
        $id = (int) $id;

        try {
            $author = $this->authorService->getAuthorById($id);

            if (!$author) {
                return JsonResponse::error('Author not found', 404);
            }

            return JsonResponse::success($author);
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
            
            $authors = $this->authorService->searchAuthors($query);
                       
            return JsonResponse::success($authors);
            
        } catch (PDOException $e) {
            return JsonResponse::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return JsonResponse::error($e->getMessage(), 500);
        }
    }
}