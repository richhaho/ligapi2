<?php

declare(strict_types=1);


namespace App\Api\Dto;


class ScannerDto
{
    public string $code;
    public ?string $itemType = null;
}
