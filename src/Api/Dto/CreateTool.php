<?php

declare(strict_types=1);

namespace App\Api\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateTool extends BaseTool implements DtoInterface
{
    /**
     * @Assert\Length(max=255)
     * @Assert\Length(min=1)
     */
    public ?string $itemNumber = null;
    
}
