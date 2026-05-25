<?php

namespace App;

class Request {
    public function getJsonBody(): array {
        $json = file_get_contents('php://input');

        return json_decode($json, true) ?? [];
    }

    public function __get($name) {
        return $_GET[$name] ?? null;
    }

    public function query(string $name, $default = null) {
        return $_GET[$name] ?? $default;
    }

    public function allQuery() : array {
        return $_GET;
    }
}