<?php

declare(strict_types=1);


namespace App\Services\Import\Transformers;


use App\Api\Dto\DtoInterface;
use App\Api\Dto\SupplierDto;

class SupplierNameTransformer implements TransformerInterface
{
    
    public function supports(string $transformer): bool
    {
        return $transformer === SupplierNameTransformer::class;
    }
    
    public function transform(array $data, string $property, DtoInterface $dto, string $title): ?SupplierDto
    {
        if (!isset($data['supplierName'])) {
            return null;
        }
        $supplierDto = new SupplierDto();
        $supplierDto->name = $data['supplierName'];
        return $supplierDto;
    }
}
