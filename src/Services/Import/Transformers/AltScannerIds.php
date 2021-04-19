<?php

declare(strict_types=1);


namespace App\Services\Import\Transformers;


use App\Api\Dto\DtoInterface;

class AltScannerIds implements TransformerInterface
{
    
    public function supports(string $transformer): bool
    {
        return $transformer === AltScannerIds::class;
    }
    
    public function transform(array $data, string $property, DtoInterface $dto, string $title): array
    {
        $altScannerIds = [];
    
        if (isset($data['ID']) && $data['ID']) {
            $altScannerIds[] = 'MAT_ID_' . $data['ID'];
        }
    
        if (isset($data['Original ID']) && $data['Original ID']) {
            $altScannerIds[] = $data['Original ID'];
        }
        
        return $altScannerIds;
    }
}
