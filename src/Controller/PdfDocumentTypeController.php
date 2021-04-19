<?php

declare(strict_types=1);


namespace App\Controller;


use App\Api\ApiContext;
use App\Api\Dto\PdfDocumentTypeDto;
use App\Api\Mapper\PdfDocumentTypeMapper;
use App\Entity\Data\ChangeAction;
use App\Entity\Data\Permission;
use App\Entity\Material;
use App\Entity\PdfDocumentType;
use App\Event\ChangeEvent;
use App\Exceptions\Domain\MissingDataException;
use App\Repository\PdfDocumentTypeRepository;
use App\Services\Pdf\DefaultPdfSpecifications\PdfSpecificationInterface;
use App\Services\Pdf\PdfSpecification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @Route(path="/api/pdfdocumenttypes/", name="api_labeltype_")
 */
class PdfDocumentTypeController
{
    /**
     * @Route(path="currentordertype", name="current_order_type", methods={"GET"})
     * @ApiContext(groups={"detail"}, selfRoute="api_labeltype_get")
     */
    public function current_order_type(PdfDocumentTypeRepository $pdfDocumentTypeRepository): ?PdfDocumentType
    {
        return $pdfDocumentTypeRepository->getOrderPdfDocumentType();
    }
    
    /**
     * @Route(name="index", methods={"GET"})
     * @ApiContext(groups={"list"}, selfRoute="api_labeltype_get")
     */
    public function index(PdfDocumentTypeRepository $repository, iterable $pdfSpecifications): iterable
    {
        $data = [];
        /** @var PdfDocumentType $item */
        foreach ($repository->findBy([], ["name" => "ASC"]) as $item) {
            /** @var PdfSpecificationInterface $pdfSpecification */
            foreach ($pdfSpecifications as $pdfSpecification) {
                $pdfSpec = $pdfSpecification->getPdfSpecification();
                if ($pdfSpec->getId() === $item->getPdfSpecificationId()) {
                    $item->setPdfSpecification($pdfSpec);
                }
            }
            if (!$item->getPdfSpecification()) {
                throw MissingDataException::forEntityNotFound($item->getPdfSpecificationId(), PdfSpecification::class);
            }
            $data[] = $item;
        }
        return $data;
    }
    
    /**
     * @Route(path="{id}", name="get", methods={"GET"})
     * @ApiContext(groups={"detail"}, selfRoute="api_labeltype_get")
     */
    public function get(PdfDocumentType $pdfDocumentType): PdfDocumentType
    {
        return $pdfDocumentType;
    }
    
    /**
     * @Route(name="create", methods={"POST"})
     * @ApiContext(groups={"list"}, selfRoute="api_labeltype_get")
     */
    public function create(
        PdfDocumentTypeDto $pdfDocumentTypeDto,
        PdfDocumentTypeMapper $pdfDocumentTypeMapper,
        EntityManagerInterface $em,
        Security $security
    ): PdfDocumentType
    {
        if(!$security->isGranted(Permission::ADMIN, Material::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        $labelType = $pdfDocumentTypeMapper->createLabelTypeFromDto($pdfDocumentTypeDto);
        $em->persist($labelType);
        $em->flush();
        
        return $labelType;
    }
    
    /**
     * @Route(path="{id}", name="put", methods={"PUT"})
     */
    public function put(
        PdfDocumentTypeDto $pdfDocumentTypeDto,
        PdfDocumentTypeMapper $pdfDocumentTypeMapper,
        PdfDocumentType $pdfDocumentType,
        EntityManagerInterface $em,
        Security $security
    ) : PdfDocumentType
    {
        if(!$security->isGranted(Permission::ADMIN, Material::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        $pdfDocumentType = $pdfDocumentTypeMapper->putLabelTypeFromDto($pdfDocumentTypeDto, $pdfDocumentType);
        $em->flush();
        return $pdfDocumentType;
    }
    
    /**
     * @Route(path="{id}", name="delete", methods={"DELETE"})
     */
    public function delete(
        PdfDocumentType $labelType,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        Security $security
    ): Response
    {
        if(!$security->isGranted(Permission::DELETE, $labelType)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        
        $entityManager->remove($labelType);
        $eventDispatcher->dispatch(new ChangeEvent(ChangeAction::delete(), $labelType));
        $entityManager->flush();
        
        return new Response(null, 204);
    }
}
