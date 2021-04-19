<?php

declare(strict_types=1);


namespace App\Services\BatchChanges\Messages;


class PatchMany
{
    /**
     * @var string[] $ids
     */
    private array $ids;
    private array $query;
    private string $entityClass;
    private string $userId;
    
    public function __construct(array $ids, array $query, string $entityClass, string $userId)
    {
        $this->ids = $ids;
        $this->query = $query;
        $this->entityClass = $entityClass;
        $this->userId = $userId;
    }
    
    public function getIds(): array
    {
        return $this->ids;
    }
    
    public function getQuery(): array
    {
        return $this->query;
    }
    
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }
    
    public function getUserId(): string
    {
        return $this->userId;
    }
    
}
