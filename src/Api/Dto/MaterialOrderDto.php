<?php

declare(strict_types=1);


namespace App\Api\Dto;


use Symfony\Component\Validator\Constraints as Assert;

class MaterialOrderDto
{
    /**
     * @Assert\NotBlank()
     * @var MaterialOrderPositionDto[] $materialOrderPositions
     */
    public array $materialOrderPositions;
    /**
     * @Assert\NotBlank()
     */
    public string $materialOrderType;
    /**
     * @Assert\NotBlank()
     */
    public SupplierDto $supplier;
    
    public ?string $deliveryNote = null;
    
    public ?string $consignmentNumber = null;
}
