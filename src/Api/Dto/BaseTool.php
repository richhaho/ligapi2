<?php

declare(strict_types=1);

namespace App\Api\Dto;

use App\Entity\Data\EntityType;
use App\Validator\Unique;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Unique(entity="App\Entity\Tool", properties={"itemNumber"})
 */
abstract class BaseTool implements BaseEntityDtoInterface
{
    public function getEntityType(): EntityType
    {
        return EntityType::tool();
    }
    
    /**
     * @Assert\Length(max=255)
     * @Assert\Length(min=1)
     */
    public ?string $name = null;
    
    /**
     * @Assert\Range(min=0)
     */
    public ?float $purchasingPrice = null;
    
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
    
    /**
     * @Assert\Length(max=255)
     */
    public ?string $blecode = null;
    
    public ?string $note = null;
    
    /**
     * @Assert\Length(max=255)
     * @Assert\Length(min=1)
     * @Assert\NotBlank()
     */
    public string $home;
    
    /**
     * @Assert\Length(max=255)
     * @Assert\Length(min=1)
     */
    public ?string $owner = null;
    
    public ?ItemGroupDto $itemGroup = null;
    
    public ?PermissionGroupDto $permissionGroup = null;
    
    public ?string $purchasingDate = null;
    
    public ?array $customFields = null;
    
    public ?array $altScannerIds = null;
    
    public ?bool $isBroken = null;
    
    public ?string $usableTill = null;
}
