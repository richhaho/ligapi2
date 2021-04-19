<?php

declare(strict_types=1);


namespace App\Services\Import\Transformers;


use App\Api\Dto\DtoInterface;

class FloatTransformer implements TransformerInterface
{
    
    public function supports(string $transformer): bool
    {
        return $transformer === FloatTransformer::class;
    }
    
    public function transform(array $data, string $property, DtoInterface $dto, string $title): ?float
    {
        if (!isset($data[$title])) {
            return null;
        }
        return (float) $data[$title];
    }
}
