<?php

declare(strict_types=1);


namespace App\Repository;


use App\Api\Dto\SearchDto;
use App\Entity\SearchableInterface;
use App\Entity\SearchIndex;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use function Symfony\Component\String\u;

class SearchIndexRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SearchIndex::class);
    }
    
    public function findSearchable(SearchableInterface $searchable): ?SearchIndex
    {
        $entityShortname = u(get_class($searchable))->afterLast('\\')->lower()->toString();
        
        return $this->createQueryBuilder('searchIndex')
            ->andWhere('searchIndex.entityId = :entityId')
            ->setParameter('entityId', $searchable->getId())
            ->andWhere('searchIndex.entityShortname = :entityShortname')
            ->setParameter('entityShortname', $entityShortname)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    
    public function getCategorizedSearchResults(SearchDto $searchDto)
    {
        $unsorted = $this->getBySearchDto($searchDto);
        
        $resultArray = [];
    
        /** @var SearchIndex $item */
        foreach ($unsorted as $item) {
            $resultArray[$item->getEntityShortname()][] = $item;
        }
        
        return $resultArray;
    }
    
    public function getBySearchDto(SearchDto $searchDto)
    {
        $query = $this->createQueryBuilder('searchIndex');
        
        $conditions = [];
        foreach ($searchDto->entityShortnames as $entityShortname) {
            $condition[] = sprintf('searchIndex.entityShortname = %s', $entityShortname);
        }
        
        $condition = implode(' OR ', $conditions);
        
        if ($condition) {
            $query->andWhere($condition);
        }
        
        return $query
            ->andWhere('searchIndex.content LIKE :searchterm')
            ->setParameter('searchterm', '%'.$searchDto->searchterm.'%')
            ->getQuery()
            ->getResult()
        ;
    }
    
}
