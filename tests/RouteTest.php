<?php
declare(strict_types=1);

namespace Northwoods\Router;

use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    use CanMockHandler;

    public function testConstructedWithMethodAndPatternAndHandler(): void
    {
        $route = new Route(
            $method = 'GET',
            $pattern = '/',
            $handler = $this->mockHandler()
        );

        $this->assertEquals($method, $route->method());
        $this->assertEquals($pattern, $route->pattern());
        $this->assertEquals($handler, $route->handler());
    }
}
