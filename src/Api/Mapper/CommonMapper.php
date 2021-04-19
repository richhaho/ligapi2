<?php

declare(strict_types=1);


namespace App\Api\Mapper;


use App\Repository\UserRepository;

class CommonMapper
{
    private iterable $mappers;
    private UserRepository $userRepository;
    
    public function __construct(iterable $mappers, UserRepository $userRepository)
    {
        $this->mappers = $mappers;
        $this->userRepository = $userRepository;
    }
    
    private function findMapper(string $entityName): ?MapperInterface
    {
        foreach ($this->mappers as $mapper) {
            if ($mapper->supports($entityName)) {
                return $mapper;
            }
        }
        return null;
    }
    
    public function supports(string $dtoName): bool
    {
        return $this->findMapper($dtoName) !== null;
    }
    
    public function createEntityFromDto($dto, string $userId): ?object
    {
        return $this->findMapper(get_class($dto))->createEntityFromDto($dto, $userId);
    }
    
    public function putEntityFromDto(object $entity, $dto): object
    {
        return $this->findMapper(get_class($dto))->putEntityFromDto($dto, $entity);
    }
    
    public function patchEntityFromDto(object $entity, $dto): object
    {
        return $this->findMapper(get_class($dto))->patchEntityFromDto($dto, $entity);
    }
    
    public function prepareImport($dto, string $userId)
    {
        $mapper = $this->findMapper(get_class($dto));
        return $mapper->prepareImport($dto, $userId);
    }
}
