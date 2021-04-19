<?php

declare(strict_types=1);


namespace App\Repository;


use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }
    
    public function findByName(string $name): ?Project
    {
        return $this->createQueryBuilder('project')
            ->where('project.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }
}
