<?php

declare(strict_types=1);


namespace App\Services\Import\Transformers\Material;


use App\Api\Dto\DtoInterface;
use App\Services\Import\Transformers\TransformerInterface;

class MaterialCategoryTransformer implements TransformerInterface
{
    
    public function supports(string $transformer): bool
    {
        return $transformer === MaterialCategoryTransformer::class;
    }
    
    public function transform(array $data, string $property, DtoInterface $dto, string $title): bool
    {
        if (isset($data['Kategorie'])) {
            return $data['Kategorie'] === 'Buchen' || $data['Kategorie'] === 'Buchen Min';
        }
        return false;
    }
}
