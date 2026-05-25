<?php

namespace App\Controllers;

use App\Route;
use App\Request;
use App\JsonResponse;

#[Route('/api/authors')]
class AuthorController {

    #[Route('/{id}', methods: ['GET'])]
    public function getAuthor(Request $request, int|string $id): JsonResponse {
        if (!is_numeric($id)) {
            return JsonResponse::error('Invalid author ID', 400);
        }
        $id = (int) $id;

        $author = [];

        return JsonResponse::success($author, 'Author found');
    }

    #[Route('', methods: ['GET'])]
    public function getAllAuthors(Request $request): JsonResponse {
        
        $authors = [];

        return JsonResponse::success([
            'author' => $authors
        ], 'Authors retrieved successfully');
    }
}