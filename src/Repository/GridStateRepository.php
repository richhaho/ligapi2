<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\GridState;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class GridStateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GridState::class);
    }
    
    public function getNonDefaultGridStates(): iterable
    {
        return $this->createQueryBuilder('gridState')
            ->andWhere('gridState.isDefault = false')
            ->getQuery()
            ->getResult()
        ;
    }
    
    public function findByTypeAndName(string $type, string $name): ?GridState
    {
        return $this->createQueryBuilder('gridState')
            ->where('gridState.name = :name')
            ->andWhere('gridState.gridType = :type')
            ->setParameter('name', $name)
            ->setParameter('type', $type)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    
    public function findDefaultByTypeAndUser(string $type, User $user): ?GridState
    {
        return $this->createQueryBuilder('gridState')
            ->andWhere('gridState.isDefault = true')
            ->andWhere('gridState.gridType = :type')
            ->andWhere('gridState.user = :user')
            ->setParameter('type', $type)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
