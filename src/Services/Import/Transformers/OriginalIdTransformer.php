<?php

declare(strict_types=1);


namespace App\Services\Import\Transformers;


use App\Api\Dto\DtoInterface;

class OriginalIdTransformer implements TransformerInterface
{
    public function supports(string $transformer): bool
    {
        return $transformer === OriginalIdTransformer::class;
    }
    
    public function transform(array $data, string $property, DtoInterface $dto, string $title): ?array
    {
        if (!property_exists($dto, 'altScannerIds')) {
            return null;
        }
        $altScannerIds = [];
        if (isset($data['id'])) {
            $altScannerIds[] = (string) $data['id'];
        }
        if (isset($data['originalId'])) {
            $altScannerIds[] = (string) $data['originalId'];
        }
        
        return $altScannerIds;
    }
}
