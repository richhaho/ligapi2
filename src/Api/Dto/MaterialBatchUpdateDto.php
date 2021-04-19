<?php

declare(strict_types=1);


namespace App\Api\Dto;


use Symfony\Component\Validator\Constraints as Assert;

class MaterialBatchUpdateDto implements DtoInterface
{
    /**
     * @Assert\NotBlank()
     */
    public string $id;
    
    public ?string $name;
    public ?string $itemGroup;
    public ?string $mainLocation;
    public ?float $mainLocationStock;
    public ?float $mainLocationAdditionalStock;
    public ?float $minStock;
    public ?float $maxStock;
    public ?string $mainSupplier;
    public ?string $mainSupplierOrderNumber;
    public ?float $mainSupplierPurchasingPrice;
    public ?float $sellingPrice;
    public ?float $orderAmount;
    public ?string $manufacturerNumber;
    public ?string $manufacturerName;
    public ?string $barcode;
    public ?string $unit;
    public ?string $permissionGroup;
    public ?string $note;
    public ?string $usableTill;
    public ?string $unitAlt;
    public ?string $profileImage;
    public ?float $unitConversion;
    public ?bool $permanentInventory;
    public ?array $customFields;
}
