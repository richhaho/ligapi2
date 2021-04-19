<?php

declare(strict_types=1);


namespace App\Api\Dto;

use App\Validator\Unique;

/**
 * @Unique(entity="App\Entity\PermissionGroup", properties={"name"})
 */
class PermissionGroupDto
{
    public ?string $id = null;
    public ?string $name = null;
}
