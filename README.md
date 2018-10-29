# Northwoods Router

[![Build Status](https://travis-ci.com/northwoods/router.svg?branch=develop)](https://travis-ci.com/northwoods/router)
[![Code Quality](https://scrutinizer-ci.com/g/northwoods/router/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/northwoods/router/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/northwoods/router/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/northwoods/router/?branch=master)
[![Latest Stable Version](http://img.shields.io/packagist/v/northwoods/router.svg?style=flat)](https://packagist.org/packages/northwoods/router)
[![Total Downloads](https://img.shields.io/packagist/dt/northwoods/router.svg?style=flat)](https://packagist.org/packages/northwoods/router)
[![License](https://img.shields.io/packagist/l/northwoods/router.svg?style=flat)](https://packagist.org/packages/northwoods/router)

A [FastRoute][fastroute] based router designed to be used with [PSR-15 middleware][psr15].

[fastroute]: https://github.com/nikic/FastRoute
[psr15]: https://www.php-fig.org/psr/psr-15/

## Installation

The best way to install and use this package is with [composer](http://getcomposer.org/):

```shell
composer require northwoods/router
```

## Usage

```php
use Northwoods\Router\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

$router = new Router();
$router->get('user.list', '/users', $userList);
$router->get('user.detail', '/users/{id:\d+}', $userDetail);
$router->post('user.create', '/users', $userCreate);

/** @var ServerRequestInterface */
$request = /* create server request */;

/** @var ResponseInterface */
$response = $router->handle($request);
```

### Usage with Middleware

The router implements `RequestHandlerInterface` and can be used with any
`MiddlewareInterface` implementation, including middleware dispatchers that
implement the middleware interface, such as [Broker][broker]:

```php
/** @var \Northwoods\Broker\Broker */
$broker = /* create the broker */;

/** @var \Northwoods\Router\Router */
$router = /* create the router */;

// Use the router as the request handler
$response = $broker->process($request, $router);
```

This is the preferred usage of the router, as it ensures that middleware wraps
the execution of application code.

[broker]: https://github.com/northwoods/broker

### Route Handlers

All route handlers MUST implement the `RequestHandlerInterface` interface:

```php
namespace Acme;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UserListHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var array */
        $users = /* load from database, etc */;

        return new Response(200, ['content-type' => 'application-json'], json_encode($users));
    }
}
```

If it is preferable to lazy load handlers, the [lazy-middleware][lazy-middleware]
package can be used:

```php
use Northwoods\Middleware\LazyHandlerFactory;

/** @var LazyHandlerFactory */
$lazyHandler = /* create the factory */;

$router->post('user.create', '/users', $lazyHandler->defer(CreateUserHandler::class));
```

[lazy-middleware]: https://github.com/northwoods/lazy-middleware

### Reverse Routing

Reverse routing enables generating URIs from routes:

```php
$uri = $router->uri('user.detail', ['id' => 100]);

assert($uri === '/users/100');
```

## API

### Router::add($name, $route);

Add a fully constructed route.

### Router::get($name, $pattern, $handler)

Add a route that works for HTTP GET requests.

### Router::post($name, $pattern, $handler)

Add a route that works for HTTP POST requests.

### Router::put($name, $pattern, $handler)

Add a route that works for HTTP PUT requests.

### Router::patch($name, $pattern, $handler)

Add a route that works for HTTP PATCH requests.

### Router::delete($name, $pattern, $handler)

Add a route that works for HTTP DELETE requests.

### Router::head($name, $pattern, $handler)

Add a route that works for HTTP HEAD requests.

### Router::options($name, $pattern, $handler)

Add a route that works for HTTP OPTIONS requests.

### Router::handle($request)

Dispatch routing for a request.

If no route is found, a response with a HTTP 404 status will be returned.

If a route is found, but it does not allow the request method, a response with
a HTTP 405 will be returned.

## Credits

Borrows some ideas from [zend-expressive-fastroute][zf-fastroute] for handling [reverse routing][zf-rr].

[zf-fastroute]: https://github.com/zendframework/zend-expressive-fastroute
[zf-rr]: https://github.com/zendframework/zend-expressive-fastroute/pull/32
