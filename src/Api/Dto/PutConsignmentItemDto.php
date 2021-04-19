<?php

declare(strict_types=1);


namespace App\Api\Dto;


use Symfony\Component\Validator\Constraints as Assert;

class PutConsignmentItemDto
{
    public ?float $amount = null;
    public ?float $consignedAmount = null;
    
    /**
     * @Assert\NotNull()
     */
    public ?string $consignmentItemStatus;
}
