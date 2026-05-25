<?php

namespace App;

class JsonResponse {
    public function __construct(
        private array $data,
        private int $statusCode = 200
    ) {}

    public function send(): void {
        http_response_code($this->statusCode);

        header('Content-Type: application/json');

        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');

        echo json_encode($this->data, JSON_UNESCAPED_UNICODE);
    }

    public static function success(array $data = [], string $message = 'Success', int $code = 200): self {
        return new self([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    public static function error(string $message = 'Error', int $code = 400, array $errors = []): self {
        return new self([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $code);
    }
}