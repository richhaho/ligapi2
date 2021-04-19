<?php

declare(strict_types=1);


namespace App\Api\Dto;


class QuickBookDto
{
    public float $amount;
    public string $code;
    public ?string $note = null;
}
