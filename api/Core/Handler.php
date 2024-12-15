<?php

namespace Api\Core;

class Handler
{
    public function __construct(
        private string $method,
        private string $path,
        private string $callback
    )
    {
        $this->callback = $callback;
    }

    public static function create(string $method, string $path, string $callback): self
    {
        return new self ($method, $path, $callback);
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getCallback(): string
    {
        return $this->callback;
    }
}