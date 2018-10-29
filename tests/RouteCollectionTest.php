<?php
declare(strict_types=1);

namespace Northwoods\Router;

use PHPUnit\Framework\TestCase;

class RouteCollectionTest extends TestCase
{
    use CanMockHandler;

    /** @var RouteCollection */
    private $collection;

    public function setUp()
    {
        $this->collection = new RouteCollection();
    }

    public function testCanSetAndGetRoutes(): void
    {
        $route = new Route('GET', '/test', $this->mockHandler());

        $this->collection->set('test', $route);

        $this->assertEquals($route, $this->collection->get('test'));
    }

    public function testCanBeCounted(): void
    {
        $route = new Route('GET', '/test', $this->mockHandler());

        $this->collection->set('test', $route);

        $this->assertCount(1, $this->collection);
    }

    public function testCanBeIterated(): void
    {
        $route = new Route('GET', '/test', $this->mockHandler());

        $this->collection->set('test', $route);

        foreach ($this->collection as $r) {
            $this->assertEquals($route, $r);
        }
    }

    public function testCanDetectNotSetRoute(): void
    {
        $this->expectException(Error\RouteNotSetException::class);

        $this->collection->get('test');
    }
}
