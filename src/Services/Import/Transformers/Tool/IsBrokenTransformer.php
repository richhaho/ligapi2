<?php

declare(strict_types=1);


namespace App\Services\Import\Transformers\Tool;


use App\Api\Dto\DtoInterface;
use App\Services\Import\Transformers\TransformerInterface;

class IsBrokenTransformer implements TransformerInterface
{
    public function supports(string $transformer): bool
    {
        return $transformer === IsBrokenTransformer::class;
    }
    
    public function transform(array $data, string $property, DtoInterface $dto, string $title): bool
    {
        if (!$data[$title]) {
            return false;
        }
        
        return $data[$title] !== 'funktioniert';
    }
}
