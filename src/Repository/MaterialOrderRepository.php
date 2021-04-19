<?php

declare(strict_types=1);


namespace App\Repository;


use App\Entity\Company;
use App\Entity\MaterialOrder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MaterialOrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MaterialOrder::class);
    }
    
    public function getNextMaterialOrder(): ?MaterialOrder
    {
        return $this->createQueryBuilder('materialOrder')
            ->andWhere("materialOrder.materialOrderStatus.value = 'new'")
            ->andWhere("materialOrder.materialOrderType.value = 'webshop'")
            ->orderBy('materialOrder.createdAt', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }
    
    public function findHighestItemNumber(?Company $company): int
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
                    select ifnull(max(CONVERT(material_order_number, SIGNED INTEGER)), 0)
                    from material_order
                    where company_id = "'.$company->getId().'"
                    and material_order_number REGEXP \'^[0-9]+$\'
                ';
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchOne();
        return (int) $result;
    }
}
