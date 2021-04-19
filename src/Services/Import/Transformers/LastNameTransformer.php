<?php

declare(strict_types=1);


namespace App\Services\Import\Transformers;


use App\Api\Dto\CreateUser;
use App\Api\Dto\DtoInterface;

class LastNameTransformer implements TransformerInterface
{
    public function supports(string $transformer): bool
    {
        return $transformer === LastNameTransformer::class;
    }
    
    public function transform(array $data, string $property, DtoInterface $dto, string $title): ?string
    {
        if ($dto instanceof CreateUser) {
            return $data[$property];
        }
        
        return null;
    }
}
