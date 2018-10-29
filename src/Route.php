<?php
declare(strict_types=1);

namespace Northwoods\Router;

use Psr\Http\Server\RequestHandlerInterface;

class Route
{
    /** @var string */
    private $method;

    /** @var string */
    private $pattern;

    /** @var RequestHandlerInterface */
    private $handler;

    public function __construct(string $method, string $pattern, RequestHandlerInterface $handler)
    {
        $this->method = $method;
        $this->pattern = $pattern;
        $this->handler = $handler;
    }

    /**
     * Get the allowed HTTP method.
     */
    public function method(): string
    {
        return $this->method;
    }

    /**
     * Get the route pattern.
     */
    public function pattern(): string
    {
        return $this->pattern;
    }

    /**
     * Get the route handler.
     */
    public function handler(): RequestHandlerInterface
    {
        return $this->handler;
    }
}
