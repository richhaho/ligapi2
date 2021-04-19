<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Company;
use App\Entity\CustomField;
use App\Entity\Keyy;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class KeyyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Keyy::class);
    }
    
    public function findAllActiveKeyys()
    {
        return $this->createQueryBuilder('keyy')
            ->where("keyy.isArchived = false")
            ->orderBy('keyy.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }
    
    public function findHighestItemNumber(Company $company): ?int
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
                    select ifnull(max(CONVERT(item_number, SIGNED INTEGER)), 0)
                    from keyy
                    where company_id = "'.$company->getId().'"
                    and item_number REGEXP \'^[0-9]+$\'
                ';
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchOne();
        return (int) $result;
    }
    
    public function findByCode(string $code)
    {
        if (str_contains($code, 'K|')) {
            $itemNumber = str_replace('K|', '', $code);
            return $this->createQueryBuilder('keyy')
                ->where('keyy.itemNumber = :itemNumber')
                ->setParameter('itemNumber', $itemNumber)
                ->getQuery()
                ->getResult()
                ;
        }
        return $this->createQueryBuilder('keyy')
            ->leftJoin('keyy.home', 'home')
            ->leftJoin('keyy.owner', 'owner')
            ->leftJoin('home.user', 'homeUser')
            ->leftJoin('owner.user', 'ownerUser')
            ->select("
                keyy.name,
                keyy.id,
                'keyy' as type
            ")
            ->Where('keyy.id = :code')
            ->orwhere('keyy.address = :code')
            ->orwhere('keyy.name = :code')
            ->orWhere('keyy.itemNumber = :code')
            ->orwhere('home.name = :code')
            ->orwhere('owner.name = :code')
            ->orwhere("concat(homeUser.firstName, ' ', homeUser.lastName) = :code")
            ->orwhere("concat(ownerUser.firstName, ' ', ownerUser.lastName) = :code")
            ->setParameter('code', $code)
            ->distinct()
            ->getQuery()
            ->getResult()
        ;
    }
    
    public function getKeyysWithCustomFieldSet(CustomField $customField)
    {
        return $this->createQueryBuilder('keyy')
            ->andWhere('keyy.customFields LIKE :customFieldId')
            ->setParameter('customFieldId', '%' . $customField->getId() . '%')
            ->getQuery()
            ->getResult()
        ;
    }
    
    public function findByAltScannerId(string $altScannerId): ?Keyy
    {
        $altScannerId = '"' . $altScannerId . '"';
        return $this->createQueryBuilder('keyy')
            ->where("keyy.altScannerIds is not null")
            ->andWhere('JSON_CONTAINS(keyy.altScannerIds, :altScannerId) = 1')
            ->setParameter('altScannerId', $altScannerId)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }
}
