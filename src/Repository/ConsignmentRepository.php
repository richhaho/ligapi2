<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Company;
use App\Entity\Consignment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ConsignmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Consignment::class);
    }
    
    public function findHighestItemNumber(Company $company): ?int
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
                    select ifnull(max(CONVERT(consignment_number, SIGNED INTEGER)), 0)
                    from consignment
                    where company_id = "'.$company->getId().'"
                    and consignment_number REGEXP \'^[0-9]+$\'
                ';
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchOne();
        return (int) $result;
    }
    
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('consignment')
            ->leftJoin('consignment.project', 'project')
            ->leftJoin('consignment.user', 'user')
            ->andWhere('project.deleted = false OR project.deleted is null')
            ->andWhere('user.deleted = false OR user.deleted is null')
            ->getQuery()
            ->getResult()
        ;
    }
}
