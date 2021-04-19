<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Data\ItemGroupType;
use App\Entity\ItemGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ItemGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ItemGroup::class);
    }
    
    public function findByNameAndType(string $name, ItemGroupType $itemGroupType)
    {
        return $this->createQueryBuilder('itemGroup')
            ->andWhere('itemGroup.name = :name')
            ->andWhere('itemGroup.itemGroupType.value = :itemGroupType')
            ->setParameter('name', $name)
            ->setParameter('itemGroupType', $itemGroupType->getValue())
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }
    
    public function findWithParams(array $params)
    {
        $resultQuery = $this->createQueryBuilder('item_group');
        if (isset($params['type'])) {
            $resultQuery = $resultQuery
                ->andWhere('item_group.itemGroupType.value = :type')
                ->setParameter('type', $params['type']);
        }
        return $resultQuery
            ->orderBy('item_group.name')
            ->getQuery()
            ->getResult();
    }
}
