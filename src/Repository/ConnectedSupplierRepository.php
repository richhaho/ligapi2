<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ConnectedSupplier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ConnectedSupplierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConnectedSupplier::class);
    }
    
    public function getByName(string $name): ?ConnectedSupplier
    {
        return $this->createQueryBuilder('connectedSupplier')
            ->where('connectedSupplier.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
