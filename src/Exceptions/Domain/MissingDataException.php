<?php


namespace App\Exceptions\Domain;


use App\Entity\Supplier;

class MissingDataException extends \InvalidArgumentException implements DomainException
{
    public static function forEntityNotFound($searchTerm, $class, ?string $type = 'id'): self
    {
        return new self(sprintf('No entity with %s %s for class %s found.', $type, $searchTerm, $class));
    }
    
    public static function forMissingData(string $type): self
    {
        return new self(sprintf('No %s provided.', $type));
    }
    
    public static function forDirectOrderOrderSourceMissing(string $orderNumber, Supplier $supplier): self
    {
        return new self(sprintf('For orderNumber %s and supplier %s was no order source found.', $orderNumber, $supplier->getName()));
    }
    
    public function getStatusCode(): int
    {
        return 404;
    }
}
