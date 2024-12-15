<?php

namespace Api\Core;

class Response
{
    private int $statusCode;
    private string $message;
    private array $data;

    public function __construct(int $statusCode, string $message, array $data = [])
    {
        $this->setStatusCode($statusCode);
        $this->message = $message;
        $this->data = $data;
    }

    public function send(): void
    {
        http_response_code($this->statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => $this->statusCode,
            'message' => $this->message,
            'data' => $this->data
        ]);
        exit;
    }

    private function setStatusCode(int $statusCode): void
    {
        if ($statusCode < 100 || $statusCode > 599) {
            throw new \InvalidArgumentException("Invalid HTTP status code: $statusCode");
        }
        $this->statusCode = $statusCode;
    }

    public static function success(string $message = 'Success', array $data = [], int $statusCode = 200): void
    {
        $response = new self($statusCode, $message, $data);
        $response->send();
    }

    public static function error(string $message = 'An error occurred', array $data = [], int $statusCode = 400,): void
    {
        $response = new self($statusCode, $message, $data);
        $response->send();
    }

    public static function notFound(string $message = 'Resource not found', array $data = []): void
    {
        $response = new self(404, $message, $data);
        $response->send();
    }

    public static function unauthorized(string $message = 'Unauthorized access', array $data = []): void
    {
        $response = new self(401, $message, $data);
        $response->send();
    }

    public static function forbidden(string $message = 'Access forbidden', array $data = []): void
    {
        $response = new self(403, $message, $data);
        $response->send();
    }

    public static function internalError(string $message = 'Internal server error', array $data = []): void
    {
        $response = new self(500, $message, $data);
        $response->send();
    }

    public static function conflict(string $message = 'Resource conflict', array $data = []): void
    {
        $response = new self(409, $message, $data);
        $response->send();
    }
}
