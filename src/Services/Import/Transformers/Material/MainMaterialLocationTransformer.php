<?php

declare(strict_types=1);


namespace App\Services\Import\Transformers\Material;


use App\Api\Dto\DtoInterface;
use App\Api\Dto\MaterialLocationDto;
use App\Services\Import\Transformers\TransformerInterface;

class MainMaterialLocationTransformer implements TransformerInterface
{
    public function supports(string $transformer): bool
    {
        return $transformer === MainMaterialLocationTransformer::class;
    }
    
    public function transform(array $data, string $property, DtoInterface $dto, string $title): array
    {
        if (($data['Hauptlager (HL)'] ?? "") === "") {
            return [];
        }
     
        $materialLocationDto = new MaterialLocationDto();
        
        if (isset($data['HL Bestand 2'])) {
            $materialLocationDto->currentStockAlt = (float) $data['HL Bestand 2'] ?? 0;
        }
        if (isset($data['HL Bestand'])) {
            $materialLocationDto->currentStock = (float) $data['HL Bestand'] ?? 0;
        }
        if (isset($data['HL Min'])) {
            $materialLocationDto->minStock = (float) $data['HL Min'] ?? 0;
        }
     
        $materialLocationDto->name = (string) $data['Hauptlager (HL)'];
        $materialLocationDto->locationCategory = 'main';
        return [$materialLocationDto];
    }
}
