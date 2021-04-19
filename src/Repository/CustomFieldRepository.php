<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CustomField;
use App\Entity\Data\EntityType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CustomFieldRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomField::class);
    }
    
    public function findByNameAndEntityType(string $name, EntityType $entityType): ?CustomField
    {
        return $this->createQueryBuilder('customField')
            ->where('customField.name = :name')
            ->andWhere('customField.entityType_value = :entityType')
            ->setParameter('entityType', $entityType->getValue())
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
