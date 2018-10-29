<?php
declare(strict_types=1);

namespace Northwoods\Router\Error;

final class RouteNotSetException extends \OutOfBoundsException
{
    public static function from(string $name): self
    {
        return new self("Route '$name' has not been set");
    }
}
