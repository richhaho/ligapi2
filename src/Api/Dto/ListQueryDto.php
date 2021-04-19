<?php

declare(strict_types=1);


namespace App\Api\Dto;

class ListQueryDto
{
    public int $startRow = 0;
    public int $endRow = 100;
    /**
     * @var FilterDto[] $filterModel
     */
    public array $filterModel = [];
    /**
     * @var SortDto[] $sortModel
     */
    public array $sortModel = [];
    public string $search = '';
}
