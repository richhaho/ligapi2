<?php

declare(strict_types=1);


namespace App\Services\BatchChanges\Messages;


use App\Api\Dto\DtoInterface;

class CreateMany
{
    /**
     * @var DtoInterface[] $dtos
     */
    private array $dtos;
    private string $userId;
    
    public function __construct(array $dtos, string $userId)
    {
        $this->dtos = $dtos;
        $this->userId = $userId;
    }
    
    public function getDtos(): array
    {
        return $this->dtos;
    }
    
    public function getUserId(): string
    {
        return $this->userId;
    }
    
    
}
