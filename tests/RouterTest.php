<?php
declare(strict_types=1);

namespace Northwoods\Router;

use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    use CanMockHandler;

    /** @var Router */
    private $router;

    public function setUp()
    {
        $this->router = new Router();
    }

    public function testExposesCollection(): void
    {
        $this->assertCount(0, $this->router->routes());
    }

    public function testCanHandleGetRequest(): void
    {
        $this->router->get('users.list', '/users', $this->mockHandler(function ($request) {
            return $request->getAttributes() === [];
        }));

        $this->router->get('users.detail', '/users/{id}', $this->mockHandler(function ($request) {
            return $request->getAttribute('id') === '22';
        }));

        $response = $this->router->handle(new ServerRequest('GET', '/users/22'));

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCanHandlePostRequests(): void
    {
        $this->router->post('users.create', '/users', $this->mockHandler());

        $response = $this->router->handle(new ServerRequest('POST', '/users'));

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCanHandlePutRequests(): void
    {
        $this->router->put('users.update', '/users', $this->mockHandler());

        $response = $this->router->handle(new ServerRequest('PUT', '/users'));

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCanHandlePatchRequests(): void
    {
        $this->router->patch('users.update', '/users', $this->mockHandler());

        $response = $this->router->handle(new ServerRequest('PATCH', '/users'));

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCanHandleDeleteRequests(): void
    {
        $this->router->delete('users.delete', '/users/{id}', $this->mockHandler());

        $response = $this->router->handle(new ServerRequest('DELETE', '/users/5'));

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCanHandleHeadRequests(): void
    {
        $this->router->head('users.check', '/users', $this->mockHandler());

        $response = $this->router->handle(new ServerRequest('HEAD', '/users'));

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCanHandleOptionsRequests(): void
    {
        $this->router->options('users.links', '/users', $this->mockHandler());

        $response = $this->router->handle(new ServerRequest('OPTIONS', '/users'));

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCanDetectNotFoundRoutes(): void
    {
        $response = $this->router->handle(new ServerRequest('POST', '/'));

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testCanDetectInvalidMethods(): void
    {
        $this->router->get('users.list', '/users', $this->mockHandler());
        $this->router->post('users.create', '/users', $this->mockHandler());

        $response = $this->router->handle(new ServerRequest('DELETE', '/users'));

        $this->assertEquals(405, $response->getStatusCode());

        $allow = preg_split('/,\s*/', $response->getHeaderLine('allow'));

        $this->assertContains('GET', $allow);
        $this->assertContains('POST', $allow);
    }

    public function testCanGenerateUri(): void
    {
        $this->router->patch('users.update', '/users/{id}', $this->mockHandler());

        $uri = $this->router->uri('users.update', ['id' => 1]);

        $this->assertEquals('/users/1', $uri);
    }

    public function testCanDetectMissingParametersWhenGeneratingUri(): void
    {
        $this->router->patch('users.update', '/users/{id}', $this->mockHandler());

        $this->expectException(Error\UriParametersMissingException::class);
        $this->expectExceptionMessage('[id]');

        $this->router->uri('users.update');
    }

    public function testCanDetectInvalidParameterWhenGeneratingUri(): void
    {
        $this->router->post('hello', '/hello/{name:a-z+}', $this->mockHandler());

        $this->expectException(Error\UriParameterInvalidException::class);
        $this->expectExceptionMessage('[name]');

        $this->router->uri('hello', ['name' => 1]);
    }
}
