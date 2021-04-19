<?php

declare(strict_types=1);


namespace App\Api\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class DirectOrderDto
{
    /**
     * @Assert\NotNull()
     * @var DirectOrderPositionDto[] $directOrderPositions
     */
    public array $directOrderPositions;
    
    /**
     * @Assert\NotNull()
     */
    public SupplierDto $mainSupplier;
    
}
