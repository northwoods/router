<?php
declare(strict_types=1);

namespace Northwoods\Router;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use FastRoute\RouteParser;
use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;
use Http\Factory\Discovery\HttpFactory;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function FastRoute\simpleDispatcher;

class Router implements
    MiddlewareInterface,
    RequestHandlerInterface,
    RequestMethodInterface,
    StatusCodeInterface
{
    /** @var RouteCollection */
    private $routes;

    /** @var RouteParser */
    private $parser;

    /** @var ResponseFactoryInterface */
    private $responseFactory;

    public function __construct(
        ?RouteCollection $routes = null,
        ?RouteParser $parser = null,
        ?ResponseFactoryInterface $responseFactory = null
    ) {
        $this->routes = $routes ?? new RouteCollection();
        $this->parser = $parser ?? new RouteParser\Std();
        $this->responseFactory = $responseFactory ?? HttpFactory::responseFactory();
    }

    /**
     * Add a named route.
     */
    public function add(string $name, Route $route): void
    {
        $this->routes->set($name, $route);
    }

    /**
     * Add a named route for a GET request.
     */
    public function get(string $name, string $pattern, RequestHandlerInterface $handler): void
    {
        $this->add($name, new Route(self::METHOD_GET, $pattern, $handler));
    }

    /**
     * Add a named route for a POST request.
     */
    public function post(string $name, string $pattern, RequestHandlerInterface $handler): void
    {
        $this->add($name, new Route(self::METHOD_POST, $pattern, $handler));
    }

    /**
     * Add a named route for a PUT request.
     */
    public function put(string $name, string $pattern, RequestHandlerInterface $handler): void
    {
        $this->add($name, new Route(self::METHOD_PUT, $pattern, $handler));
    }

    /**
     * Add a named route for a PATCH request.
     */
    public function patch(string $name, string $pattern, RequestHandlerInterface $handler): void
    {
        $this->add($name, new Route(self::METHOD_PATCH, $pattern, $handler));
    }

    /**
     * Add a named route for a DELETE request.
     */
    public function delete(string $name, string $pattern, RequestHandlerInterface $handler): void
    {
        $this->add($name, new Route(self::METHOD_DELETE, $pattern, $handler));
    }

    /**
     * Add a named route for a HEAD request.
     */
    public function head(string $name, string $pattern, RequestHandlerInterface $handler): void
    {
        $this->add($name, new Route(self::METHOD_HEAD, $pattern, $handler));
    }

    /**
     * Add a named route for a OPTIONS request.
     */
    public function options(string $name, string $pattern, RequestHandlerInterface $handler): void
    {
        $this->add($name, new Route(self::METHOD_OPTIONS, $pattern, $handler));
    }

    /**
     * Get the route collection.
     */
    public function routes(): RouteCollection
    {
        return $this->routes;
    }

    /**
     * Generate a URI for a route.
     *
     * @throws Error\UriParameterInvalidException If a parameter has an invalid value.
     * @throws Error\UriParametersMissingException If required parameters are missing.
     */
    public function uri(string $name, array $params = []): string
    {
        // Use the route parser to generate a URI for a named route:
        // https://github.com/nikic/FastRoute/issues/66
        $routes = $this->parser->parse($this->routes->get($name)->pattern());
        $missing = [];

        foreach (array_reverse($routes) as $parts) {
            $missing = $this->findMissingParameters($parts, $params);

            // Try the next route
            if (count($missing) > 0) {
                continue;
            }

            $path = '';
            foreach ($parts as $part) {
                // Fixed segment
                if (is_string($part)) {
                    $path .= $part;
                    continue;
                }

                // Check if the parameter can be matched
                if (! preg_match("~^{$part[1]}$~", strval($params[$part[0]]))) {
                    throw Error\UriParameterInvalidException::from($part[0], $part[1]);
                }

                // Variable segment
                $path .= $params[$part[0]];
            }

            return $path;
        }

        throw Error\UriParametersMissingException::from($name, $missing, array_keys($params));
    }

    // MiddlewareInterface
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // https://github.com/nikic/FastRoute#usage
        $dispatcher = $this->makeDispatcher();
        $match = $dispatcher->dispatch($request->getMethod(), $request->getUri()->getPath());

        if ($match[0] === Dispatcher::NOT_FOUND) {
            return $handler->handle($request);
        }

        if ($match[0] === Dispatcher::METHOD_NOT_ALLOWED) {
            return $this->responseFactory
                        ->createResponse(self::STATUS_METHOD_NOT_ALLOWED)
                        ->withHeader('allow', implode(',', $match[1]));
        }

        foreach ($match[2] as $param => $value) {
            $request = $request->withAttribute($param, $value);
        }

        return $match[1]->handler()->handle($request);
    }

    // RequestHandlerInterface
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->process($request, new NotFoundHandler($this->responseFactory));
    }

    /** @return string[] */
    private function findMissingParameters(array $parts, array $params): array
    {
        // Remove all fixed segments, get named parts
        $missing = array_column(array_filter($parts, 'is_array'), 0);

        return array_diff($missing, array_keys($params));
    }

    private function makeDispatcher(): Dispatcher
    {
        return simpleDispatcher(function (RouteCollector $collector): void {
            foreach ($this->routes as $route) {
                $collector->addRoute($route->method(), $route->pattern(), $route);
            }
        });
    }
}
