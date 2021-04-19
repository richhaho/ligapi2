<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Material;
use App\Entity\StockChange;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class StockChangeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StockChange::class);
    }
    
    public function getStockChangesOfMaterial(Material $material)
    {
        return $this->createQueryBuilder('stockChange')
            ->leftJoin('stockChange.materialLocation', 'materialLocation')
            ->andWhere('materialLocation.material = :material')
            ->setParameter('material', $material->getId())
            ->getQuery()
            ->getResult()
        ;
    }
    
    public function findByOriginalId(string $originalId): ?StockChange
    {
        return $this->createQueryBuilder('stockChange')
            ->where('stockChange.originalId = :originalId')
            ->setParameter('originalId', $originalId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
