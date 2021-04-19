<?php

declare(strict_types=1);


namespace App\Repository;


use App\Entity\OwnerChange;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class OwnerChangeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OwnerChange::class);
    }
    
    public function getWithQuery(array $query)
    {
        $queryBuilder = $this->createQueryBuilder('ownerChange');
        
        $ownerChangesType = $query['ownerChangesType'] ?? null;
        
        if ($ownerChangesType === 'tool') {
            $queryBuilder->andWhere('ownerChange.tool is not null');
        }
        if ($ownerChangesType === 'keyy') {
            $queryBuilder->andWhere('ownerChange.keyy is not null');
        }
        
        $queryBuilder->addOrderBy('ownerChange.createdAt', 'DESC');
        
        return $queryBuilder
            ->getQuery()
            ->getResult()
        ;
    }
}
