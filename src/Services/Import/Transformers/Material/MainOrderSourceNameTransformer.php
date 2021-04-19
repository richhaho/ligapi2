<?php

declare(strict_types=1);


namespace App\Services\Import\Transformers\Material;


use App\Api\Dto\DtoInterface;
use App\Api\Dto\OrderSourceDto;
use App\Api\Dto\SupplierDto;
use App\Services\Import\Transformers\TransformerInterface;

class MainOrderSourceNameTransformer implements TransformerInterface
{
    
    public function supports(string $transformer): bool
    {
        return $transformer === MainOrderSourceNameTransformer::class;
    }
    
    public function transform(array $data, string $property, DtoInterface $dto, string $title): array
    {
        if (($data['Hauptbezugsquelle (HB)'] ?? "") === "" && ($data['HB Bestellnummer'] ?? "") === "") {
            return [];
        }
        $orderSourceDto = new OrderSourceDto();
        $supplierDto = new SupplierDto();
        $supplierDto->name = $data['Hauptbezugsquelle (HB)'];
        $orderSourceDto->supplier = $supplierDto;
        if (isset($data['HB EK netto'])) {
            $orderSourceDto->price = (float) $data['HB EK netto'];
        }
        if (isset($data['HB Menge je Bestelleinheit'])) {
            $orderSourceDto->amountPerPurchaseUnit = (float) $data['HB Menge je Bestelleinheit'];
        }
        if (isset($data['HB Bestellnummer'])) {
            $orderSourceDto->orderNumber = (string) $data['HB Bestellnummer'];
        }
        return [$orderSourceDto];
    }
}
