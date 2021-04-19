<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\MaterialOrderPosition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MaterialOrderPositionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MaterialOrderPosition::class);
    }
}
