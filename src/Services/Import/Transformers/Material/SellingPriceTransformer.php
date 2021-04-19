<?php

declare(strict_types=1);


namespace App\Services\Import\Transformers\Material;


use App\Api\Dto\DtoInterface;
use App\Services\Import\Transformers\TransformerInterface;

class SellingPriceTransformer implements TransformerInterface
{
    
    public function supports(string $transformer): bool
    {
        return $transformer === SellingPriceTransformer::class;
    }
    
    public function transform(array $data, string $property, DtoInterface $dto, string $title)
    {
        return '';
    }
}
