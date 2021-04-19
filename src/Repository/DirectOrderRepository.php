<?php

declare(strict_types=1);


namespace App\Repository;


use App\Entity\Company;
use App\Entity\DirectOrder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DirectOrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DirectOrder::class);
    }
    
    public function findHighestItemNumber(Company $company): ?int
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
                    select ifnull(max(CONVERT(direct_order_number, SIGNED INTEGER)), 0)
                    from direct_order
                    where company_id = "'.$company->getId().'"
                    and direct_order_number REGEXP \'^[0-9]+$\'
                ';
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchOne();
        return (int) $result;
    }
}
