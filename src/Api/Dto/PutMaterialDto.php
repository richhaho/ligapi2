<?php

declare(strict_types=1);

namespace App\Api\Dto;

class PutMaterialDto extends BaseMaterial implements DtoInterface
{
    public ?string $id = null;
 
    // For Import
    public ?string $originalId = null;
    
    public ?string $orderStatus = null;
}
