<?php
declare(strict_types=1);

namespace Northwoods\Router\Error;

final class UriParameterInvalidException extends \InvalidArgumentException
{
    public static function from(string $param, string $pattern): self
    {
        return new self("Value of parameter [$param] failed to match '$pattern'");
    }
}
