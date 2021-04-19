<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\MaterialLocation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MaterialLocationRepository extends ServiceEntityRepository
{
    const MAINCATEGORY = 'main';
    
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MaterialLocation::class);
    }
    
    public function getMaterialLocationsOfMaterial(string $materialId)
    {
        return $this->createQueryBuilder('materialLocation')
            ->andWhere('materialLocation.material = :material')
            ->setParameter('material', $materialId)
            ->getQuery()
            ->getResult()
            ;
    }
    
    public function getMainMaterialLocationsOfMaterial(string $materialId)
    {
        return $this->createQueryBuilder('materialLocation')
            ->andWhere('materialLocation.material = :material')
            ->setParameter('material', $materialId)
            ->andWhere('materialLocation.locationCategory.value = :mainCategory')
            ->setParameter('mainCategory', self::MAINCATEGORY)
            ->getQuery()
            ->getResult()
            ;
    }
    
    public function findByMaterialIdAndLocationId(string $materialId, string $locationId): ?MaterialLocation
    {
        return $this->createQueryBuilder('materialLocation')
            ->andWhere('materialLocation.material = :materialId')
            ->andWhere('materialLocation.location = :locationId')
            ->setParameter('materialId', $materialId)
            ->setParameter('locationId', $locationId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    
    public function findByOriginalId(?string $originalId): ?MaterialLocation
    {
        return $this->createQueryBuilder('materialLocation')
            ->andWhere('materialLocation.originalId = :originalId')
            ->setParameter('originalId', $originalId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
