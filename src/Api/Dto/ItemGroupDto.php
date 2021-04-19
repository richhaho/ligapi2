<?php

declare(strict_types=1);


namespace App\Api\Dto;


use App\Validator\Unique;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Unique(entity="App\Entity\ItemGroup", properties={"name", "type"}, targets={"name", "itemGroupType.value"})
 */
class ItemGroupDto
{
    public ?string $id = null;
    
    /**
     * @Assert\Length(max=255)
     * @Assert\NotNull()
     */
    public ?string $name = null;
    
    /**
     * @Assert\NotNull()
     */
    public string $itemGroupType;
}
