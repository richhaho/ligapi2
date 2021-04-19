<?php

declare(strict_types=1);


namespace App\Api\Dto;


class FilterDto
{
    public $filter = '';
    public string $filterType;
    public string $type = '';
    public array $values = [];
}
