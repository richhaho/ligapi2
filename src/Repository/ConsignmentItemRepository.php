<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ConsignmentItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ConsignmentItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConsignmentItem::class);
    }
    
    public function getConsignmentId(string $id): ?string
    {
        return $this->createQueryBuilder('consignmentItem')
            ->leftJoin('consignmentItem.consignment', 'consignment')
            ->select('consignment.id')
            ->where('consignmentItem.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
