<?php
declare(strict_types=1);

namespace Northwoods\Router;

use Fig\Http\Message\StatusCodeInterface;
use Http\Factory\Discovery\HttpFactory;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

class NotFoundHandler implements RequestHandlerInterface
{
    /** @var ResponseFactoryInterface */
    private $responseFactory;

    public function __construct(
        ?ResponseFactoryInterface $responseFactory = null
    ) {
        $this->responseFactory = $responseFactory ?? HttpFactory::responseFactory();
    }

    // RequestHandlerInterface
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->responseFactory->createResponse(StatusCodeInterface::STATUS_NOT_FOUND);
    }
}
