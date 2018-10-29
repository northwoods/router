<?php
declare(strict_types=1);

namespace Northwoods\Router\Error;

final class UriParametersMissingException extends \InvalidArgumentException
{
    /**
     * @param string[] $missing
     * @param string[] $params
     */
    public static function from(string $name, array $missing, array $params): self
    {
        $missing = implode(',', $missing);
        $params = implode(',', $params);

        return new self("Route '$name' expects parameters [$missing] but received [$params]");
    }
}
