<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PermissionGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PermissionGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PermissionGroup::class);
    }
    
    public function findByName(string $name)
    {
        return $this->createQueryBuilder('permissionGroup')
            ->where('permissionGroup.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }
}
