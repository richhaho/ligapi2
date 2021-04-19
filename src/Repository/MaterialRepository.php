<?php

declare(strict_types=1);

namespace App\Repository;

use App\Api\Dto\ListQueryDto;
use App\Api\Dto\SearchDto;
use App\Entity\Company;
use App\Entity\CustomField;
use App\Entity\Material;
use App\Entity\SearchIndex;
use App\Repository\Query\ListQueryBuilder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

class MaterialRepository extends ServiceEntityRepository
{
    private ListQueryBuilder $listQueryBuilder;
    private SearchIndexRepository $searchIndexRepository;
    
    public function __construct(ManagerRegistry $registry, ListQueryBuilder $listQueryBuilder, SearchIndexRepository $searchIndexRepository)
    {
        parent::__construct($registry, Material::class);
        $this->listQueryBuilder = $listQueryBuilder;
        $this->searchIndexRepository = $searchIndexRepository;
    }
    
//    private function addSearch(QueryBuilder $queryBuilder, string $searchterm): void
//    {
//        $queryBuilder
//            ->andWhere('
//                    material.id LIKE :searchterm OR
//                    material.barcode LIKE :searchterm OR
//                    material.name LIKE :searchterm OR
//                    material.manufacturerName LIKE :searchterm OR
//                    material.manufacturerNumber LIKE :searchterm OR
//                    material.itemNumber LIKE :searchterm OR
//                    material.manufacturerNumber LIKE :searchterm OR
//                    location.name LIKE :searchterm OR
//                    user.firstName LIKE :searchterm OR
//                    user.lastName LIKE :searchterm OR
//                    orderSources.orderNumber LIKE :searchterm
//                ')
//            ->setParameter('searchterm', '%'.$searchterm.'%');
//    }
    
    private function addSearchQuery(QueryBuilder $queryBuilder, string $searchterm): bool
    {
        $searchDto = new SearchDto();
        $searchDto->searchterm = $searchterm;
        $searchDto->entityShortnames = ['material'];
    
        $searchResult = $this->searchIndexRepository->getBySearchDto($searchDto);
    
        $conditions = [];
    
        /** @var SearchIndex $item */
        foreach ($searchResult as $item) {
            $conditions[] = sprintf('material.id = \'%s\'', $item->getEntityId());
        }
    
        if (count($conditions) === 0) {
            return false;
        }
    
        $condition = implode(' OR ', $conditions);
    
        $queryBuilder->andWhere($condition);
        
        return true;
    }
    
    public function filter(ListQueryDto $listQueryDto): iterable
    {
        $materialQueryBuilder = $this->createQueryBuilder('material');

        $this->listQueryBuilder->buildListQuery($listQueryDto, $materialQueryBuilder);
        
        if ($listQueryDto->search && !$this->addSearchQuery($materialQueryBuilder, $listQueryDto->search)) {
            return [];
        }
        
        $query = $materialQueryBuilder
            ->select(
                'material',
                'materialLocations',
                'itemGroups',
                'permissionGroup',
                'supplier',
                'orderSources',
                'tasks',
                'location',
                'user'
            )
            ->andWhere("material.isArchived = false")
            ->leftJoin('material.materialLocations', 'materialLocations')
            ->leftJoin('material.itemGroup', 'itemGroups')
            ->leftJoin('material.permissionGroup', 'permissionGroup')
            ->leftJoin('material.orderSources', 'orderSources')
            ->leftJoin('material.tasks', 'tasks')
            ->leftJoin('orderSources.supplier', 'supplier')
            ->leftJoin('materialLocations.location', 'location')
            ->leftJoin('location.user', 'user')
//            ->getQuery()
//            ->getResult()
        ;
    
        $paginator = new Paginator($query, $fetchJoinCollection = true);
        
        return $paginator->getQuery()->getResult();
    }
    
    public function rowsCount(ListQueryDto $listQueryDto): int
    {
        $queryBuilder = $this->createQueryBuilder('material');

        $this->listQueryBuilder->buildCountQuery($listQueryDto, $queryBuilder);
    
        if ($listQueryDto->search && !$this->addSearchQuery($queryBuilder, $listQueryDto->search)) {
            return 0;
        }
        
        return (int) $queryBuilder
            ->select('count(distinct material.id)')
            ->andWhere("material.isArchived = false")
            ->leftJoin('material.materialLocations', 'materialLocations')
            ->leftJoin('material.itemGroup', 'itemGroups')
            ->leftJoin('material.permissionGroup', 'permissionGroup')
            ->leftJoin('material.orderSources', 'orderSources')
            ->leftJoin('material.tasks', 'tasks')
            ->leftJoin('orderSources.supplier', 'supplier')
            ->leftJoin('materialLocations.location', 'location')
            ->leftJoin('location.user', 'user')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
    
    public function findByitemNumber(string $number)
    {
        return $this->createQueryBuilder('material')
            ->where('material.itemNumber = :number')
            ->setParameter('number', $number)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    
    public function findAllActiveMaterials(array $query)
    {
//        $config = $this->getEntityManager()->getConfiguration();
//        $config->addCustomStringFunction('UNSIGNED', 'App\Doctrine\CastAsUnsignedQuery');
        
        $resultQuery = $this->createQueryBuilder('material');

        $resultQuery
            ->select(
                'material',
                'materialLocations',
                'itemGroups',
                'permissionGroup',
                'supplier',
                'orderSources',
                'tasks'
            )
            ->andWhere("material.isArchived = false")
            ->andWhere("material.deleted = false")
            ->leftJoin('material.materialLocations', 'materialLocations')
            ->leftJoin('material.itemGroup', 'itemGroups')
            ->leftJoin('material.permissionGroup', 'permissionGroup')
            ->leftJoin('material.autoSupplier', 'supplier')
            ->leftJoin('material.orderSources', 'orderSources')
            ->leftJoin('material.tasks', 'tasks')
        ;
        
//        if (isset($params['page'])) {
//            $resultQuery
//                ->setFirstResult($params['page'] * 50)
//                ->setMaxResults(50)
//                ->orderBy('UNSIGNED(material.itemNumber)')
//            ;
//        }
        
        if (isset($query['itemGroup'])) {
            $resultQuery->andWhere('material.itemGroup = :itemGroup')
                ->setParameter('itemGroup', $query['itemGroup'])
            ;
        }
        
        if (isset($query['orderStatus'])) {
            $resultQuery->andWhere('material.orderStatus.value = :orderStatus')
                ->setParameter('orderStatus', $query['orderStatus'])
            ;
        }
        
        return $resultQuery
            ->andWhere("material.isArchived = false")
            ->orderBy('material.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
    
    public function findHighestItemNumber(Company $company): ?int
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
                    select ifnull(max(CONVERT(item_number, SIGNED INTEGER)), 0)
                    from material
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
        if (str_contains($code, 'M|')) {
            $itemNumber = str_replace('M|', '', $code);
            return $this->createQueryBuilder('material')
                ->where('material.itemNumber = :itemNumber')
                ->setParameter('itemNumber', $itemNumber)
                ->getQuery()
                ->getResult()
            ;
        }
        
        $query = $this->createQueryBuilder('material')
            ->leftJoin('material.materialLocations', 'materialLocations')
            ->leftJoin('materialLocations.location', 'location')
            ->leftJoin('location.user', 'user')
            ->leftJoin('material.orderSources', 'orderSources')
            ->select("
                material.name,
                material.id,
                'material' as type
            ")
            ->where('material.id = :code')
            ->orWhere('material.barcode = :code')
            ->orwhere('material.name = :code')
            ->orWhere('material.itemNumber = :code')
            ->orWhere('material.altScannerIds is not null and JSON_CONTAINS(material.altScannerIds, :altCode) = 1')
            ->orWhere('material.manufacturerNumber = :code')
            ->orWhere('location.name = :code')
            ->orwhere("concat(user.firstName, ' ', user.lastName) = :code")
            ->orWhere('orderSources.orderNumber = :code')
            ->setParameter('code', $code)
            ->setParameter('altCode', '"' . $code . '"')
            ->distinct()
            ->getQuery()
        ;
        
        return $query->getResult();
    }
    
    public function findByAltScannerId(string $altScannerId): ?Material
    {
        $altScannerId = '"' . $altScannerId . '"';
        return $this->createQueryBuilder('material')
            ->where("material.altScannerIds is not null")
            ->andWhere('JSON_CONTAINS(material.altScannerIds, :altScannerId) = 1')
            ->setParameter('altScannerId', $altScannerId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    
    public function findMaterialByCode(string $code): array
    {
        if (str_contains($code, 'M|')) {
            $itemNumber = str_replace('M|', '', $code);
            return $this->createQueryBuilder('material')
                ->where('material.itemNumber = :itemNumber')
                ->setParameter('itemNumber', $itemNumber)
                ->getQuery()
                ->getResult()
                ;
        }
        return $this->createQueryBuilder('material')
            ->leftJoin('material.materialLocations', 'materialLocations')
            ->leftJoin('materialLocations.location', 'location')
            ->leftJoin('location.user', 'user')
            ->leftJoin('material.orderSources', 'orderSources')
            ->where('material.id = :code')
            ->orWhere('material.barcode = :code')
            ->orwhere('material.name = :code')
            ->orWhere('material.itemNumber = :code')
            ->orWhere('material.altScannerIds LIKE :id')
            ->orWhere('material.manufacturerNumber = :code')
            ->orWhere('location.name = :code')
            ->orwhere("concat(user.firstName, ' ', user.lastName) = :code")
            ->orWhere('orderSources.orderNumber = :code')
            ->setParameter('code', $code)
            ->setParameter('id', '%' . $code . '%')
            ->distinct()
            ->getQuery()
            ->getResult()
        ;
    }
    
    public function getOldestMaterialWithAutoSearchTerm()
    {
        return $this->createQueryBuilder('material')
            ->andWhere("material.autoSearchTerm is not null")
            ->andWhere("material.autoStatus.value = 'new'")
            ->orderBy('material.createdAt', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }
    
    public function getMaterialsWithAutoSearchTerm(Company $company, int $limit = null): iterable
    {
        return array_column($this->createQueryBuilder('material')
            ->select('material.id')
            ->andWhere("material.autoSearchTerm is not null")
            ->andWhere("material.autoStatus.value = 'new'")
            ->andWhere('material.company = :company')
            ->orderBy('material.createdAt', 'ASC')
            ->setParameter('company', $company)
            ->setMaxResults($limit)
            ->getQuery()
            ->getScalarResult(), 'id');
    }
    
    public function getMaterialsWithCustomFieldSet(CustomField $customField)
    {
        return $this->createQueryBuilder('material')
            ->andWhere('material.customFields LIKE :customFieldId')
            ->setParameter('customFieldId', '%' . $customField->getId() . '%')
            ->getQuery()
            ->getResult()
        ;
    }
    
    public function findAllArchivedMaterials()
    {
        $resultQuery = $this->createQueryBuilder('material');
    
        $resultQuery
            ->select(
                'material',
                'materialLocations',
                'itemGroups',
                'permissionGroup',
                'supplier',
                'orderSources',
                'tasks'
            )
            ->leftJoin('material.materialLocations', 'materialLocations')
            ->leftJoin('material.itemGroup', 'itemGroups')
            ->leftJoin('material.permissionGroup', 'permissionGroup')
            ->leftJoin('material.autoSupplier', 'supplier')
            ->leftJoin('material.orderSources', 'orderSources')
            ->leftJoin('material.tasks', 'tasks')
        ;
        
        return $resultQuery
            ->andWhere("material.isArchived = true")
            ->getQuery()
            ->getResult();
    }
}
