<?php

declare(strict_types=1);


namespace App\Repository\Query;


use App\Api\Dto\FilterDto;
use Doctrine\ORM\QueryBuilder;

class ListFilterSetFragment implements ListFilterFragment
{
    
    public function supports(FilterDto $filterDto): bool
    {
        return $filterDto->filterType === 'set';
    }
    
    public function apply(QueryBuilder $queryBuilder, FilterDto $filterDto, string $column): void
    {
        foreach ($filterDto->values as $i => $value) {
            $searchtermVar = 'searchterm' . $i;
            $queryBuilder->orWhere(sprintf('%s = :%s', $column, $searchtermVar))
                ->setParameter($searchtermVar, $value);
        }
    }
}
