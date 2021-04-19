<?php

declare(strict_types=1);

namespace App\Api\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateMaterialDto extends BaseMaterial implements DtoInterface
{
    /**
     * @Assert\Length(min=1)
     * @Assert\Length(max=255)
     */
    public ?string $itemNumber = null;
    
    /**
     * @Assert\Range(min=1)
     */
    public ?float $amountPerPurchaseUnit = null;

    public ?string $autoSearchTerm = null;
    public ?ConnectedSupplierDto $autoSupplier = null;
    public ?string $autoStatus = null;
    public ?string $orderStatus = null;
}
