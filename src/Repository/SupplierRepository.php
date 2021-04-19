<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Data\OrderStatus;
use App\Entity\Supplier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SupplierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Supplier::class);
    }
    
    public function getNames(): array
    {
        $result = $this->createQueryBuilder('supplier')
            ->select('supplier.name')
            ->distinct(true)
            ->andWhere('supplier.deleted = false')
            ->getQuery()
            ->getArrayResult()
        ;
        
        return array_map(function($supplier) {
            return $supplier['name'];
        }, $result);
    }
    
    public function findByName(?string $name): ?Supplier
    {
        return $this->createQueryBuilder('supplier')
            ->where('supplier.name = :name')
            ->andWhere('supplier.deleted = false')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    
    public function findWithToOrder(): array
    {
        $orderStatusValue = OrderStatus::toOrder()->getValue();
        return $this->createQueryBuilder('supplier')
            ->leftJoin('supplier.orderSources', 'orderSources')
            ->leftJoin('orderSources.material', 'material')
            ->andWhere('material.orderStatus.value = :orderStatusValue')
            ->setParameter('orderStatusValue', $orderStatusValue)
            ->orderBy('supplier.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
