<?php

declare(strict_types=1);

namespace App\Api\Dto;

use App\Entity\Data\EntityType;
use App\Validator\Unique;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Unique(entity="App\Entity\Keyy", properties={"itemNumber"})
 */
abstract class BaseKeyy implements BaseEntityDtoInterface
{
    public function getEntityType(): EntityType
    {
        return EntityType::keyy();
    }
    
    /**
     * @Assert\Range(min=1)
     */
    public ?int $amount = null;

    /**
     * @Assert\Length(max=255)
     * @Assert\Length(min=1)
     */
    public ?string $name = null;

    /**
     * @Assert\Length(max=255)
     */
    public ?string $address = null;
    
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
    
    public ?PermissionGroupDto $permissionGroup = null;

    public ?string $note = null;
    
    public ?array $customFields = null;
    
    /**
     * @var FileDto[] $files
     */
    public ?array $files = [];
    
    public ?array $altScannerIds = null;
}
