<?php

declare(strict_types=1);


namespace App\Services\Import\Transformers;


use App\Api\Dto\DtoInterface;
use App\Api\Dto\PutMaterialDto;

class MaterialIdTransformer implements TransformerInterface
{
    public function supports(string $transformer): bool
    {
        return $transformer === MaterialIdTransformer::class;
    }
    
    public function transform(array $data, string $property, DtoInterface $dto, string $title): ?PutMaterialDto
    {
        if (!$data['materialId']) {
            return null;
        }
        
        $materialDto = new PutMaterialDto();
        $materialDto->originalId = (string) $data['materialId'];
        
        return $materialDto;
    }
}
