<?php

declare(strict_types=1);


namespace App\Services\Import\Transformers;


use App\Api\Dto\CreateKeyy;
use App\Api\Dto\DtoInterface;
use App\Api\Dto\OrderSourceDto;
use App\Api\Dto\StockChangeDto;

class AmountTransformer implements TransformerInterface
{
    
    public function supports(string $transformer): bool
    {
        return $transformer === AmountTransformer::class;
    }
    
    public function transform(array $data, string $property, DtoInterface $dto, string $title): ?float
    {
        if ($dto instanceof CreateKeyy) {
            return (float) $data[$title];
        }
        if ($dto instanceof OrderSourceDto) {
            return (float) $data[$title];
        }
        if ($dto instanceof StockChangeDto) {
            return (float) $data[$title];
        }
        return null;
    }
}
