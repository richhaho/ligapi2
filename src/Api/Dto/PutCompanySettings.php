<?php

declare(strict_types=1);


namespace App\Api\Dto;


class PutCompanySettings
{
    public ?array $appSettings = null;
    public ?string $customMaterialName = null;
    public ?string $customToolName = null;
    public ?string $customKeyyName = null;
    public ?string $customMaterialsName = null;
    public ?string $customToolsName = null;
    public ?string $customKeyysName = null;
}
