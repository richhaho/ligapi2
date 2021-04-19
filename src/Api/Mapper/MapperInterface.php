<?php


namespace App\Api\Mapper;


use App\Api\Dto\DtoInterface;

interface MapperInterface
{
    public function supports(string $dtoName): bool;
    
    public function createEntityFromDto(DtoInterface $dto, string $userId);
    
    public function putEntityFromDto(DtoInterface $dto, object $entity);
    
    public function patchEntityFromDto(DtoInterface $dto, object $entity);
    
    public function prepareImport(DtoInterface $dto, string $userId);
}
