<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Company;
use App\Entity\CustomField;
use App\Entity\Tool;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ToolRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tool::class);
    }
    
    public function findAllActiveTools(array $query)
    {
        $resultQuery = $this->createQueryBuilder('tool');
        if (isset($query['itemGroup'])) {
            $resultQuery->andWhere('tool.itemGroup = :itemGroup')
                ->setParameter('itemGroup', $query['itemGroup'])
            ;
        }
        return $resultQuery
            ->andWhere("tool.isArchived = false")
            ->orderBy('tool.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }
    
    public function findAllArchivedTools(array $query)
    {
        $resultQuery = $this->createQueryBuilder('tool');
        if (isset($query['itemGroup'])) {
            $resultQuery->andWhere('tool.itemGroup = :itemGroup')
                ->setParameter('itemGroup', $query['itemGroup'])
            ;
        }
        return $resultQuery
            ->andWhere("tool.isArchived = true")
            ->getQuery()
            ->getResult()
            ;
    }
    
    public function findHighestItemNumber(Company $company): ?int
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
                    select ifnull(max(CONVERT(item_number, SIGNED INTEGER)), 0)
                    from tool
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
        if (str_contains($code, 'T|')) {
            $itemNumber = str_replace('T|', '', $code);
            return $this->createQueryBuilder('tool')
                ->where('tool.itemNumber = :itemNumber')
                ->setParameter('itemNumber', $itemNumber)
                ->getQuery()
                ->getResult()
            ;
        }
        return $this->createQueryBuilder('tool')
            ->leftJoin('tool.home', 'home')
            ->leftJoin('tool.owner', 'owner')
            ->leftJoin('home.user', 'homeUser')
            ->leftJoin('owner.user', 'ownerUser')
            ->select("
                tool.name,
                tool.id,
                'tool' as type
            ")
            ->where('tool.id = :code')
            ->orWhere('tool.barcode = :code')
            ->orwhere('tool.name = :code')
            ->orwhere('tool.name = :code')
            ->orWhere('tool.itemNumber = :code')
            ->orWhere('tool.manufacturerNumber = :code')
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
    
    public function getToolsWithCustomFieldSet(CustomField $customField)
    {
        return $this->createQueryBuilder('tool')
            ->andWhere('tool.customFields LIKE :customFieldId')
            ->setParameter('customFieldId', '%' . $customField->getId() . '%')
            ->getQuery()
            ->getResult()
        ;
    }
    
    public function findByAltScannerId(string $altScannerId): ?Tool
    {
        $altScannerId = '"' . $altScannerId . '"';
        return $this->createQueryBuilder('tool')
            ->where("tool.altScannerIds is not null")
            ->andWhere('JSON_CONTAINS(tool.altScannerIds, :altScannerId) = 1')
            ->setParameter('altScannerId', $altScannerId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
