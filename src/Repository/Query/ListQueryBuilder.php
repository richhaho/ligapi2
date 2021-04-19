<?php

declare(strict_types=1);


namespace App\Repository\Query;


use App\Api\Dto\ListQueryDto;
use Doctrine\ORM\QueryBuilder;

class ListQueryBuilder
{
    private iterable $fragments;
    
    public function __construct(iterable $fragments)
    {
        $this->fragments = $fragments;
    }
    
    public function buildListQuery(ListQueryDto $queryDto, QueryBuilder $queryBuilder): void
    {
        $this->buildFilterFragmentQuery($queryDto, $queryBuilder);

        $queryBuilder->setMaxResults($queryDto->endRow - $queryDto->startRow + 1);
        $queryBuilder->setFirstResult($queryDto->startRow);

        foreach ($queryDto->sortModel as $item) {
            $queryBuilder->addOrderBy(sprintf('UNSIGNED(%s)', $item->colId), $item->sort);
            $queryBuilder->addOrderBy($item->colId, $item->sort);
        }
    }

    public function buildCountQuery(ListQueryDto $queryDto, QueryBuilder $queryBuilder): void
    {
        $this->buildFilterFragmentQuery($queryDto, $queryBuilder);
    }

    private function buildFilterFragmentQuery(ListQueryDto $queryDto, QueryBuilder $queryBuilder): void
    {
        foreach ($queryDto->filterModel as $index => $item) {
            /** @var ListFilterFragment $fragment */
            foreach ($this->fragments as $fragment) {
                if ($fragment->supports($item)) {
                    $fragment->apply($queryBuilder, $item, $index);
                }
            }
        }
    }
}
