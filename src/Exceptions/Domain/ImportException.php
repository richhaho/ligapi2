<?php


namespace App\Exceptions\Domain;


class ImportException extends \InvalidArgumentException implements DomainException
{
    public static function forInvalidFileType(string $filename): self
    {
        return new self(sprintf('Filetype of %s is not supported.', $filename));
    }
}
