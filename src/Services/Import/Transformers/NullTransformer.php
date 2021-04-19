<?php

declare(strict_types=1);


namespace App\Services\Import\Transformers;


use App\Api\Dto\DtoInterface;

class NullTransformer implements TransformerInterface
{
    
    public function supports(string $transformer): bool
    {
        return $transformer === NullTransformer::class;
    }
    
    public function transform(array $data, string $property, DtoInterface $dto, string $title): ?float
    {
        if (!isset($data[$title])) {
            return null;
        }
        return null;
    }
}
