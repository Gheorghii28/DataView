<?php

use Api\Core\Router;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase {

    private $router;

    protected function setUp(): void
    {
        $this->router = new Router();
    }

    /**
     * Access private or protected properties of a class.
     *
     * @param object $object   The object whose property should be accessed.
     * @param string $property The name of the private or protected property.
     * @return mixed           The value of the property.
     */
    protected function getPrivateProperty(object $object, string $property)
    {
        $reflection = new ReflectionClass($object);
        $prop = $reflection->getProperty($property);
        $prop->setAccessible(true);

        return $prop->getValue($object);
    }


    public function testGet(): void
    {
        $method = 'GET';
        $path = '/test';
        $handler = 'TestController::test';
        $this->router->get($path, $handler);
        $handlers = $this->getPrivateProperty($this->router, 'handlers');

        $this->assertEquals($method, $handlers[0]->getMethod());
        $this->assertEquals($path, $handlers[0]->getPath());
        $this->assertEquals($handler, $handlers[0]->getCallback());
    }

    public function testPost(): void
    {
        $method = 'POST';
        $path = '/test';
        $handler = 'TestController::test';
        $this->router->post($path, $handler);
        $handlers = $this->getPrivateProperty($this->router, 'handlers');

        $this->assertEquals($method, $handlers[0]->getMethod());
        $this->assertEquals($path, $handlers[0]->getPath());
        $this->assertEquals($handler, $handlers[0]->getCallback());
    }

    public function testPut(): void
    {
        $method = 'PUT';
        $path = '/test';
        $handler = 'TestController::test';
        $this->router->put($path, $handler);
        $handlers = $this->getPrivateProperty($this->router, 'handlers');

        $this->assertEquals($method, $handlers[0]->getMethod());
        $this->assertEquals($path, $handlers[0]->getPath());
        $this->assertEquals($handler, $handlers[0]->getCallback());
    }

    public function testDelete(): void
    {
        $method = 'DELETE';
        $path = '/test';
        $handler = 'TestController::test';
        $this->router->delete($path, $handler);
        $handlers = $this->getPrivateProperty($this->router, 'handlers');

        $this->assertEquals($method, $handlers[0]->getMethod());
        $this->assertEquals($path, $handlers[0]->getPath());
        $this->assertEquals($handler, $handlers[0]->getCallback());
    }

    public function testRun(): void
    {
        $method = 'GET';
        $path = '/test';
        $handler = 'TestController::test';
        $this->router->get($path, $handler);

        ob_start();
        $this->router->run($method, $path);
        $output = ob_get_clean();
    
        $this->assertEquals('Expected Output', $output);
    }
}

class TestController
{
    public function test(array $requestData): void
    {
        echo 'Expected Output';
    }
}
