<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Customer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CustomerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }
    
    public function getByName(string $customerName): ?Customer
    {
        return $this->createQueryBuilder('customer')
            ->where('customer.name = :customerName')
            ->setParameter('customerName', $customerName)
            ->andWhere('customerName.deleted = false')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    
}
