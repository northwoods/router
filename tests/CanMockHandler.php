<?php
declare(strict_types=1);

namespace Northwoods\Router;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Nyholm\Psr7\Response;

trait CanMockHandler
{
    private function mockHandler(callable $assertion = null): RequestHandlerInterface
    {
        return new class($assertion) implements RequestHandlerInterface
        {
            /** @var callable */
            private $assertion;

            public function __construct(?callable $assertion)
            {
                $this->assertion = $assertion ?? function ($request) {
                    return true;
                };
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new Response(($this->assertion)($request) ? 200 : 400);
            }
        };
    }
}
