<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Data\OrderStatus;
use App\Entity\Material;
use App\Entity\OrderSource;
use App\Entity\Supplier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class OrderSourceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderSource::class);
    }
    
    public function getWithQuery(array $query): iterable
    {
        $queryBuilder = $this->createQueryBuilder('orderSource')
        ->select(
            'orderSource',
            'material'
        );
        
        if (isset($query['orderStatus'])) {
            $queryBuilder
                ->andWhere('material.orderStatus.value = :orderStatus')
                ->setParameter('orderStatus', $query['orderStatus'])
            ;
        }
        
        return $queryBuilder
            ->leftJoin('orderSource.material', 'material')
            ->getQuery()
            ->getResult()
        ;
    }
    
    public function getOrderSourcesOfMaterial(Material $material)
    {
        return $this->createQueryBuilder('orderSource')
            ->andWhere('orderSource.material = :material')
            ->setParameter('material', $material->getId())
            ->getQuery()
            ->getResult()
            ;
    }
    
    public function getOrderSourceOfMaterialAndSupplier(Material $material, Supplier $supplier)
    {
        return $this->createQueryBuilder('orderSource')
            ->andWhere('orderSource.material = :materialId')
            ->setParameter('materialId', $material->getId())
            ->andWhere('orderSource.supplier = :supplierId')
            ->setParameter('supplierId', $supplier->getId())
            ->getQuery()
            ->getOneOrNullResult();
    }
    
    public function getOrderSourceOfMaterialIdAndSupplierId(string $materialId, string $supplierName): ?OrderSource
    {
        return $this->createQueryBuilder('orderSource')
            ->leftJoin('orderSource.supplier', 'supplier')
            ->andWhere('orderSource.material = :materialId')
            ->setParameter('materialId', $materialId)
            ->andWhere('supplier.name = :supplierName')
            ->setParameter('supplierName', $supplierName)
            ->getQuery()
            ->getOneOrNullResult();
    }
    
    public function getOldestOrderSourceWithPriceUpdate(): ?OrderSource
    {
        return $this->createQueryBuilder('orderSource')
            ->andWhere("orderSource.autoStatus.value = 'new'")
            ->orderBy('orderSource.lastAutoSet', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    
    public function getOrderSourceIdsWithPriceUpdateForSupplier(Supplier $supplier, int $limit = null): iterable
    {
        return array_column($this->createQueryBuilder('orderSource')
            ->select('orderSource.id')
            ->andWhere("orderSource.autoStatus.value = 'new'")
            ->andWhere('orderSource.supplier = :supplierId')
            ->orderBy('orderSource.lastAutoSet', 'ASC')
            ->setParameter('supplierId', $supplier->getId())
            ->setMaxResults($limit)
            ->getQuery()
            ->getScalarResult(), 'id');
    }
    
    public function getOrdersourceOfSupplierAndOrderNumber(Supplier $supplier, string $orderNumber): ?OrderSource
    {
        return $this->createQueryBuilder('orderSource')
            ->andWhere('orderSource.orderNumber = :orderNumber')
            ->andWhere('orderSource.supplier = :supplierId')
            ->setParameter('supplierId', $supplier->getId())
            ->setParameter('orderNumber', $orderNumber)
            ->getQuery()
            ->getOneOrNullResult();
    }
    
    public function getOrderSourcesOfMaterialWithOrderStatus(OrderStatus $orderStatus)
    {
        return $this->createQueryBuilder('orderSource')
            ->leftJoin('orderSource.material', 'material')
            ->andWhere('material.orderStatus.value = :orderStatus')
            ->setParameter('orderStatus', $orderStatus->getValue())
            ->addOrderBy(sprintf('UNSIGNED(%s)', 'material.itemNumber'), 'ASC')
            ->addOrderBy('material.itemNumber', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
