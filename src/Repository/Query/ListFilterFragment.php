<?php


namespace App\Repository\Query;


use App\Api\Dto\FilterDto;
use Doctrine\ORM\QueryBuilder;

interface ListFilterFragment
{
    public function supports(FilterDto $filterDto): bool;
    
    public function apply(QueryBuilder $queryBuilder, FilterDto $filterDto, string $column): void;
}
