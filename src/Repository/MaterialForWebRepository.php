<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Material;
use App\Entity\MaterialForWeb;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MaterialForWebRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MaterialForWeb::class);
    }
    
    public function findByMaterial(Material $material): ?MaterialForWeb
    {
        return $this->createQueryBuilder('materialForWeb')
            ->andWhere('materialForWeb.materialId = :materialId')
            ->setParameter('materialId', $material->getId())
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    
}
