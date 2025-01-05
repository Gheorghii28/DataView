<?php

use Api\Core\Handler;
use PHPUnit\Framework\TestCase;

class Handlertest extends TestCase
{
    private $handler;

    public function setUp(): void
    {
        $method = 'GET';
        $path = '/test';
        $callback = 'TestController::testGet';
        $this->handler = new Handler($method, $path, $callback);
    }
    
    public function testCreate(): void
    {
        $method = 'POST';
        $path = '/test';
        $callback = 'TestController::testPost';
        $createResult = $this->handler->create($method, $path, $callback);

        $this->assertInstanceOf(Handler::class, $createResult);
    }

    public function testGetMethod(): void
    {
        $method = 'GET';

        $this->assertEquals($method, $this->handler->getMethod());
    }

    public function testGetPath(): void
    {
        $path = '/test';

        $this->assertEquals($path, $this->handler->getPath());
    }

    public function testGetCallback(): void
    {
        $callback = 'TestController::testGet';

        $this->assertEquals($callback, $this->handler->getCallback());
    }
}