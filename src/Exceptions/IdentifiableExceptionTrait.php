<?php


namespace App\Exceptions;


use Ramsey\Uuid\Uuid;

trait IdentifiableExceptionTrait
{
    private ?string $id = null;
    
    public function getId(): string
    {
        if (null === $this->id) {
            $this->id = Uuid::uuid4()->toString();
        }
        return $this->id;
    }
}
