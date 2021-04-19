<?php

declare(strict_types=1);

namespace App\Controller;

use App\Api\ApiContext;
use App\Api\Dto\CreateConsignmentItemDto;
use App\Api\Dto\ManyDto;
use App\Api\Dto\PdfDocumentDto;
use App\Api\Dto\PutConsignmentItemDto;
use App\Api\Mapper\ConsignmentItemMapper;
use App\Entity\Data\ChangeAction;
use App\Entity\Data\Permission;
use App\Entity\ConsignmentItem;
use App\Entity\Keyy;
use App\Entity\Material;
use App\Entity\Tool;
use App\Event\ChangeEvent;
use App\Exceptions\Domain\MissingDataException;
use App\Repository\ConsignmentItemRepository;
use App\Services\ConsignmentService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @Route(path="/api/consignmentitems/", name="api_consignment_item_")
 */
class ConsignmentItemController
{
    /**
     * @Route(path="print", name="print", methods={"POST"})
     */
    public function print(PdfDocumentDto $labelDto, ConsignmentService $consignmentService): string
    {
        return $consignmentService->generateConsignmentPdf($labelDto);
    }
    
    /**
     * @Route(name="index", methods={"GET"})
     * @ApiContext(groups={"list"}, selfRoute="api_consignment_item_get")
     */
    public function index(ConsignmentItemRepository $repository): iterable
    {
        return $repository->findAll();
    }
    
    /**
     * @Route(path="{id}", name="get", methods={"GET"})
     * @ApiContext(groups={"detail"}, selfRoute="api_consignment_item_get")
     */
    public function get(ConsignmentItem $consignmentItem): ConsignmentItem
    {
        return $consignmentItem;
    }
    
    /**
     * @Route(name="create", methods={"POST"})
     * @ApiContext(groups={"list"}, selfRoute="api_consignment_item_get")
     */
    public function create(
        CreateConsignmentItemDto $createConsignmentItem,
        ConsignmentItemMapper $mapper,
        EntityManagerInterface $em,
        Security $security
    ): ConsignmentItem
    {
        if ($createConsignmentItem->materialId) {
            if(!$security->isGranted(Permission::BOOK, Material::PERMISSION)) {
                throw new AccessDeniedHttpException('Fehlende Berechtigung.');
            }
        }
        if ($createConsignmentItem->toolId) {
            if(!$security->isGranted(Permission::BOOK, Tool::PERMISSION)) {
                throw new AccessDeniedHttpException('Fehlende Berechtigung.');
            }
        }
        if ($createConsignmentItem->keyyId) {
            if(!$security->isGranted(Permission::BOOK, Keyy::PERMISSION)) {
                throw new AccessDeniedHttpException('Fehlende Berechtigung.');
            }
        }
        $consignmentItem = $mapper->createConsignmentItemFromDto($createConsignmentItem);
        $em->persist($consignmentItem);
        $em->flush();
        
        return $consignmentItem;
    }
    
    /**
     * @Route(path="{id}", name="put", methods={"PUT"})
     */
    public function put(
        PutConsignmentItemDto $consignmentItemDto,
        ConsignmentItemMapper $mapper,
        ConsignmentItem $consignmentItem,
        EntityManagerInterface $em,
        Security $security
    ) : ConsignmentItem
    {
        if(!$security->isGranted(Permission::BOOK, $consignmentItem->getConsignmentItemSubject())) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        $consignmentItem = $mapper->putConsignmentItemFromDto($consignmentItemDto, $consignmentItem);
        $em->flush();
        return $consignmentItem;
    }
    
    /**
     * @Route(path="{id}", name="delete", methods={"DELETE"})
     */
    public function delete(
        ConsignmentItem $consignmentItem,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        Security $security
    ): Response
    {
        if(!$security->isGranted(Permission::DELETE, $consignmentItem->getConsignmentItemSubject())) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        
        $entityManager->remove($consignmentItem);
        $eventDispatcher->dispatch(new ChangeEvent(ChangeAction::delete(), $consignmentItem));
        $entityManager->flush();
        
        return new Response(null, 204);
    }
    
    /**
     * @Route(name="bookFromMainStock", path="bookfrommainstock", methods={"POST"})
     * @ApiContext(groups={"details"})
     */
    public function book_from_main_stock(
        ManyDto $manyDto,
        Security $security,
        ConsignmentItemRepository $consignmentItemRepository,
        ConsignmentService $consignmentService,
        EntityManagerInterface $entityManager
    ): iterable
    {
        $consignmentItems = [];
        foreach ($manyDto->ids as $id) {
            $consignmentItem = $consignmentItemRepository->find($id);
            if (!$consignmentItem) {
                throw MissingDataException::forEntityNotFound($id, ConsignmentItem::class);
            }
            if(!$security->isGranted(Permission::BOOK, $consignmentItem->getConsignmentItemSubject())) {
                throw new AccessDeniedHttpException('Fehlende Berechtigung.');
            }
            $consignmentItems[] = $consignmentItem;
        }
        $materials = $consignmentService->bookConsignmentItemsFromMainStock($consignmentItems);
        $entityManager->flush();
        return $materials;
    }
    
    /**
     * @Route(name="resolve", path="resolve", methods={"POST"})
     */
    public function resolve(
        ManyDto $manyDto,
        Security $security,
        ConsignmentItemRepository $consignmentItemRepository,
        ConsignmentService $consignmentService,
        EntityManagerInterface $entityManager
    ): Response
    {
        $consignmentItems = [];
        foreach ($manyDto->ids as $id) {
            $consignmentItem = $consignmentItemRepository->find($id);
            if (!$consignmentItem) {
                throw MissingDataException::forEntityNotFound($id, ConsignmentItem::class);
            }
            if(!$security->isGranted(Permission::BOOK, $consignmentItem->getConsignmentItemSubject())) {
                throw new AccessDeniedHttpException('Fehlende Berechtigung.');
            }
            $consignmentItems[] = $consignmentItem;
        }
        
        $consignmentService->resolveConsignmentItems($consignmentItems);
        
        $entityManager->flush();
        
        return new Response(null, 204);
    }
    
    /**
     * @Route(name="deletemultiple", path="deletemultiple", methods={"POST"})
     */
    public function deletemultiple(
        ManyDto $manyDto,
        Security $security,
        ConsignmentItemRepository $consignmentItemRepository,
        EventDispatcherInterface $eventDispatcher,
        EntityManagerInterface $entityManager
    ): Response
    {
        foreach ($manyDto->ids as $id) {
            $consignmentItem = $consignmentItemRepository->find($id);
            if (!$consignmentItem) {
                throw MissingDataException::forEntityNotFound($id, ConsignmentItem::class);
            }
            if(!$security->isGranted(Permission::BOOK, $consignmentItem->getConsignmentItemSubject())) {
                throw new AccessDeniedHttpException('Fehlende Berechtigung.');
            }
            
            $entityManager->remove($consignmentItem);
        }
    
        $eventDispatcher->dispatch(new ChangeEvent(ChangeAction::delete(), $consignmentItem));
        
        $entityManager->flush();
        
        return new Response(null, 204);
    }
}
