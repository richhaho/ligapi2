<?php


namespace App\Exceptions\Domain;


class UnsupportedMethodException extends \InvalidArgumentException implements DomainException
{
    public static function forUnsupportedMethod(string $name): self
    {
        return new self(sprintf('Mehod %s is not supported.', $name));
    }
}
