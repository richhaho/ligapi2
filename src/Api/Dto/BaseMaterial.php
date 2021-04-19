<?php

declare(strict_types=1);

namespace App\Api\Dto;

use App\Entity\Data\EntityType;
use App\Validator\Unique;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Unique(entity="App\Entity\Material", properties={"itemNumber"})
 */
abstract class BaseMaterial implements BaseEntityDtoInterface
{
    public function getEntityType(): EntityType
    {
        return EntityType::material();
    }
    
    /**
     * @Assert\Length(max=255)
     */
    public ?string $manufacturerNumber = null;
    
    /**
     * @Assert\Length(max=255)
     */
    public ?string $manufacturerName = null;
    
    /**
     * @Assert\Length(max=255)
     */
    public ?string $barcode = null;
    
    public ?bool $permanentInventory = true;
    
    /**
     * @Assert\Length(max=255)
     */
    public ?string $unit = null;
    
    /**
     * @Assert\Length(max=255)
     */
    public ?string $unitAlt = null;
    
    /**
     * @Assert\Range(min=0)
     */
    public ?float $unitConversion = null;
    
    public ?string $note = null;
    
    public ?string $usableTill = null;
    
    /**
     * @Assert\Range(min=0)
     */
    public ?float $sellingPrice = null;
    
    public ?ItemGroupDto $itemGroup = null;
    
    public ?PermissionGroupDto $permissionGroup = null;
    
    /**
     * @Assert\Length(max=255)
     */
    public ?string $name = null;
    
    /**
     * @Assert\Length(max=255)
     */
    public ?string $autoSearchTerm = null;
    
    /**
     * @Assert\Length(max=255)
     */
    public ?string $orderStatusNote = null;
    
    public ?array $customFields = null;
    
    public ?array $altScannerIds = null;
    
    /**
     * @var MaterialLocationDto[] $materialLocations
     */
    public ?array $materialLocations = [];
    
    /**
     * @var OrderSourceDto[] $orderSources
     */
    public ?array $orderSources = [];
    
    /**
     * @Assert\Range(min=0)
     */
    public ?float $orderAmount = null;
    
    public ?string $profileImageUrl = null;
}
