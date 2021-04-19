<?php

declare(strict_types=1);

namespace App\Api\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class PutKeyy extends BaseKeyy
{
    /**
     * @Assert\NotNull()
     */
    public string $id;
    
    public ?string $originalId = null;
}
