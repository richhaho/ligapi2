<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Location;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LocationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Location::class);
    }
    
    private function findByFullName(string $fullName)
    {
        return $this->createQueryBuilder('location')
            ->leftJoin('location.user', 'user')
            ->where("CONCAT(user.firstName, ' ', user.lastName) = :fullName")
            ->setParameter('fullName', $fullName)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    private function findByLocationName(string $name)
    {
        return $this->createQueryBuilder('location')
            ->where('location.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    public function findOneByName(string $name)
    {
        return $this->findByFullName($name) ?? $this->findByLocationName($name);
    }
    
    public function findWithParams(array $params)
    {
        $resultQuery = $this->createQueryBuilder('location');
        if (isset($params['type']) && $params['type'] === 'material') {
            $resultQuery = $resultQuery
                ->leftJoin('location.materialLocations', 'materialLocations')
                ->leftJoin('materialLocations.material', 'material')
                ->andWhere('material.isArchived = 0')
            ;
        }
        return $resultQuery
            ->getQuery()
            ->getResult();
    }
}
