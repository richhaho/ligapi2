<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\DirectOrderPositionResult;
use App\Entity\Supplier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DirectOrderPositionResultRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DirectOrderPositionResult::class);
    }
    
    public function getOldestNewDirectOrderPositionResult(): ?DirectOrderPositionResult
    {
        return $this->createQueryBuilder('directOrderPositionResult')
            ->andWhere("directOrderPositionResult.autoStatus.value = 'new'")
            ->orderBy('directOrderPositionResult.createdAt', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    
    public function getDirectOrderPositionResultsToProcess(Supplier $supplier, ?int $limit = null): array
    {
        return array_column($this->createQueryBuilder('directOrderPositionResult')
            ->leftJoin('directOrderPositionResult.orderSource', 'orderSource')
            ->leftJoin('orderSource.supplier', 'supplier')
            ->select('directOrderPositionResult.id')
            ->andWhere("directOrderPositionResult.autoStatus.value = 'new'")
            ->andWhere('supplier = :supplierId')
            ->orderBy('directOrderPositionResult.createdAt', 'ASC')
            ->setParameter('supplierId', $supplier->getId())
            ->setMaxResults($limit)
            ->getQuery()
            ->getScalarResult(), 'id');
    }
}
