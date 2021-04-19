<?php

declare(strict_types=1);


namespace App\Services\Import\Transformers;


use App\Api\Dto\CreateUser;
use App\Api\Dto\DtoInterface;
use App\Api\Dto\MaterialLocationDto;

class FirstNameTransformer implements TransformerInterface
{
    public function supports(string $transformer): bool
    {
        return $transformer === FirstNameTransformer::class;
    }
    
    public function transform(array $data, string $property, DtoInterface $dto, string $title): ?string
    {
        if ($dto instanceof CreateUser) {
            return $data[$property];
        }
        
        if ($dto instanceof MaterialLocationDto && isset($data['firstName']) && isset($data['lastName']) && $data['firstName'] && $data['lastName']) {
            return $data['firstName'] . ' ' . $data['lastName'];
        }
        
        return null;
    }
}
