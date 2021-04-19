<?php

declare(strict_types=1);


namespace App\Api\Dto;


class ManyDto
{
    public array $ids;
    public ?array $query = [];
}
