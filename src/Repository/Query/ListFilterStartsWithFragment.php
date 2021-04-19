<?php

declare(strict_types=1);


namespace App\Repository\Query;


use App\Api\Dto\FilterDto;
use Doctrine\ORM\QueryBuilder;

class ListFilterStartsWithFragment implements ListFilterFragment
{
    
    public function supports(FilterDto $filterDto): bool
    {
        return $filterDto->type === 'startswith';
    }
    
    public function apply(QueryBuilder $queryBuilder, FilterDto $filterDto, string $column): void
    {
        $queryBuilder->andWhere(sprintf('%s LIKE :searchterm', $column))
            ->setParameter('searchterm', '%'.$filterDto->filter);
    }
}
