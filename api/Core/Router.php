<?php

namespace Api\Core;

class Router
{
    private const METHOD_GET = 'GET';
    private const METHOD_POST = 'POST';
    private const METHOD_PUT = 'PUT';
    private const METHOD_DELETE = 'DELETE';
    private array $handlers;
    private $notFoundHandler;

    public function get(string $path, string $handler): void
    {
        $this->handlers[] = Handler::create(self::METHOD_GET, $path, $handler);
    }

    public function post(string $path, string $handler): void
    {
        $this->handlers[] = Handler::create(self::METHOD_POST, $path, $handler);
    }

    public function put(string $path, string $handler): void
    {
        $this->handlers[] = Handler::create(self::METHOD_PUT, $path, $handler);
    }

    public function delete(string $path, string $handler): void
    {
        $this->handlers[] = Handler::create(self::METHOD_DELETE, $path, $handler);
    }

    private function getRequestData(): array
    {
        $rawData = file_get_contents('php://input'); 
        $decodedData = json_decode($rawData, true); 

        if (json_last_error() !== JSON_ERROR_NONE) {
            $decodedData = [];
        }

        return array_merge($_GET, $_POST, $decodedData ?? []);
    }

    public function run(string $method, string $path): void
    {
        $requestUri = parse_url($path);
        $requestPath = $requestUri['path'];
        $callback = null;

        foreach ($this->handlers as $handler) {
            if ($handler->getPath() === $requestPath && $method === $handler->getMethod()) {
                $callback = $handler->getCallback();
            }
        }

        if (null === $callback) {
            $callback = $this->notFoundHandler;
        }

        if (is_string($callback)) {
            $parts = explode('::', $callback);
            $class = $parts[0];
            $handler = new $class;

            $method = $parts[1];
            $callback = [$handler, $method];
        }

        $requestData = $this->getRequestData();
        
        call_user_func_array($callback, [
            $requestData
        ]);
    }

    public function addNotFoundHandler(callable $handler): void
    {
        $this->notFoundHandler = $handler;
    }
}