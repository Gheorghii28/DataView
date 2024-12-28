<?php

namespace Api\Core;

class Response
{
    private int $statusCode;
    private string $message;
    private array $data;
    private bool $shouldReturn;

    public function __construct(int $statusCode = 400, string $message = 'Resource not found', array $data = [], bool $shouldReturn = false)
    {
        $this->setStatusCode($statusCode);
        $this->message = $message;
        $this->data = $data;
        $this->shouldReturn = $shouldReturn;
    }

    private function send(): array|string
    {
        $response = [
            'status' => $this->statusCode,
            'message' => $this->message,
            'data' => $this->data,
        ];

        if ($this->shouldReturn) {
            return $response;
        }

        http_response_code($this->statusCode);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    private function setStatusCode(int $statusCode): void
    {
        if ($statusCode < 100 || $statusCode > 599) {
            throw new \InvalidArgumentException("Invalid HTTP status code: $statusCode");
        }
        $this->statusCode = $statusCode;
    }

    public function success(string $message = 'Success', array $data = [], int $statusCode = 200): array|string
    {
        $shouldReturn = $this->shouldReturn;
        $response = new self($statusCode, $message, $data, $shouldReturn);
        return $response->send();
    }

    public function error(string $message = 'An error occurred', array $data = [], int $statusCode = 400): array|string
    {
        $shouldReturn = $this->shouldReturn;
        $response = new self($statusCode, $message, $data, $shouldReturn);
        return $response->send();
    }

    public function notFound(string $message = 'Resource not found', array $data = [], int $statusCode = 404): array|string
    {
        $shouldReturn = $this->shouldReturn;
        $response = new self($statusCode, $message, $data, $shouldReturn);
        return $response->send();
    }

    public function unauthorized(string $message = 'Unauthorized access', array $data = [], int $statusCode = 401): array|string
    {
        $shouldReturn = $this->shouldReturn;
        $response = new self($statusCode, $message, $data, $shouldReturn);
        return $response->send();
    }

    public function forbidden(string $message = 'Access forbidden', array $data = [], int $statusCode = 403): array|string
    {
        $shouldReturn = $this->shouldReturn;
        $response = new self($statusCode, $message, $data, $shouldReturn);
        return $response->send();
    }

    public function internalError(string $message = 'Internal server error', array $data = [], int $statusCode = 500): array|string
    {
        $shouldReturn = $this->shouldReturn;
        $response = new self($statusCode, $message, $data, $shouldReturn);
        return $response->send();
    }

    public function conflict(string $message = 'Resource conflict', array $data = [], int $statusCode = 409): array|string
    {
        $shouldReturn = $this->shouldReturn;
        $response = new self($statusCode, $message, $data, $shouldReturn);
        return $response->send();
    }
}
