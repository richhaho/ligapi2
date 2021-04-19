<?php

declare(strict_types=1);


namespace App\Services\Import\Transformers;


use App\Api\Dto\DtoInterface;
use App\Api\Dto\PermissionGroupDto;

class PermissionGroupTransformer implements TransformerInterface
{
    
    public function supports(string $transformer): bool
    {
        return $transformer === PermissionGroupTransformer::class;
    }
    
    public function transform(array $data, string $property, DtoInterface $dto, string $title): ?PermissionGroupDto
    {
        if (!isset($data[$title])) {
            return null;
        }
        $permissionGroupDto = new PermissionGroupDto();
        $permissionGroupDto->name = $data[$title];
        return $permissionGroupDto;
    }
}
