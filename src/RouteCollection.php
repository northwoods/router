<?php
declare(strict_types=1);

namespace Northwoods\Router;

final class RouteCollection implements
    \Countable,
    \IteratorAggregate
{
    /** @var Route[] */
    private $routes = [];

    /**
     * Check if a route exists in the collection.
     */
    public function has(string $name): bool
    {
        return isset($this->routes[$name]);
    }

    /**
     * Get a route from the collection.
     */
    public function get(string $name): Route
    {
        if (! $this->has($name)) {
            throw Error\RouteNotSetException::from($name);
        }

        return $this->routes[$name];
    }

    /**
     * Set a route in the collection.
     */
    public function set(string $name, Route $route): void
    {
        $this->routes[$name] = $route;
    }

    // Countable
    public function count(): int
    {
        return count($this->routes);
    }

    // IteratorAggregate
    public function getIterator(): iterable
    {
        foreach ($this->routes as $name => $route) {
            yield $route;
        }
    }
}
