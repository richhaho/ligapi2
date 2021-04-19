<?php

declare(strict_types=1);


namespace App\Api\Dto;


class SearchDto
{
    public string $searchterm;
    public array $entityShortnames = [];
}
