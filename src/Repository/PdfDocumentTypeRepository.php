<?php

declare(strict_types=1);


namespace App\Repository;


use App\Entity\PdfDocumentType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PdfDocumentTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PdfDocumentType::class);
    }
    
    public function getCount(string $entityType): string
    {
        return $this->createQueryBuilder('pdfDocumentType')
            ->select('count(pdfDocumentType.id)')
            ->where('pdfDocumentType.entityType.value = :entityType')
            ->setParameter('entityType', $entityType)
            ->getQuery()
            ->getSingleScalarResult();
    }
    
    public function getOrderPdfDocumentType(): ?PdfDocumentType
    {
        return $this->createQueryBuilder('pdfDocumentType')
            ->where("pdfDocumentType.pdfSpecificationType.value = 'order'")
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
