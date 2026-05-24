<?php

require_once '../Route.php';
require_once '../Request.php';
require_once '../JsonResponse.php';

#[Route('/api/authors')]
class AuthorController {

    #[Route('/id', methods: ['GET'])]
    public function getAuthor(Request $request, int $id): JsonResponse {
        $author = [];

        return JsonResponse::success($author, 'Author found');
    }

    public function getAllAuthors(Request $request): JsonResponse {
        
        $authors = [];

        return JsonResponse::success([
            'author' => $authors
        ], 'Authors retrieved successfully');
    }
}