<?php

declare(strict_types=1);

namespace App\Api\Dto;

class PutTool extends BaseTool implements DtoInterface
{
    public ?string $id = null;
    
    public ?string $originalId = null;
}
