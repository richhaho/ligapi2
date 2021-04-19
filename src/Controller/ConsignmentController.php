<?php

declare(strict_types=1);

namespace App\Controller;

use App\Api\ApiContext;
use App\Api\Dto\ConsignmentAddMultiple;
use App\Api\Dto\CreateConsignmentDto;
use App\Api\Dto\PutConsignmentDto;
use App\Api\Mapper\ConsignmentMapper;
use App\Entity\Data\ChangeAction;
use App\Entity\Data\Permission;
use App\Entity\Consignment;
use App\Entity\Keyy;
use App\Entity\Material;
use App\Entity\Tool;
use App\Event\ChangeEvent;
use App\Repository\ConsignmentRepository;
use App\Services\ConsignmentService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @Route(path="/api/consignments/", name="api_consignment_")
 */
class ConsignmentController
{
    /**
     * @Route(name="index", methods={"GET"})
     * @ApiContext(groups={"list"}, selfRoute="api_consignment_get")
     */
    public function index(ConsignmentRepository $repository): iterable
    {
        return $repository->findAllActive();
    }
    
    /**
     * @Route(path="{id}/empty", name="empty", methods={"POST"})
     */
    public function empty(Consignment $consignment, EntityManagerInterface $entityManager, EventDispatcherInterface $eventDispatcher): Response
    {
        foreach ($consignment->getConsignmentItems() as $item) {
            $eventDispatcher->dispatch(new ChangeEvent(ChangeAction::delete(), $item));
            $entityManager->remove($item);
        }
        $entityManager->flush();
        return new Response(null, 204);
    }
    
    /**
     * @Route(path="{id}", name="get", methods={"GET"})
     * @ApiContext(groups={"detail"}, selfRoute="api_consignment_get")
     */
    public function get(Consignment $consignment): Consignment
    {
        return $consignment;
    }
    
    /**
     * @Route(name="create", methods={"POST"})
     * @ApiContext(groups={"list"}, selfRoute="api_consignment_get")
     */
    public function create(
        CreateConsignmentDto $createConsignment,
        ConsignmentMapper $mapper,
        EntityManagerInterface $em,
        Security $security
    ): Consignment
    {
        if(
            !$security->isGranted(Permission::BOOK, Material::PERMISSION) &&
            !$security->isGranted(Permission::BOOK, Tool::PERMISSION) &&
            !$security->isGranted(Permission::BOOK, Keyy::PERMISSION)
        ) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        $consignment = $mapper->createConsignmentFromDto($createConsignment);
        $em->persist($consignment);
        $em->flush();

        return $consignment;
    }
    
    /**
     * @Route(path="{id}", name="put", methods={"PUT"})
     */
    public function put(
        PutConsignmentDto $putConsignment,
        ConsignmentMapper $mapper,
        Consignment $consignment,
        EntityManagerInterface $em) : Consignment
    {
        $consignment = $mapper->putConsignmentFromDto($putConsignment, $consignment);
        $em->flush();
        return $consignment;
    }
    
    /**
     * @Route(path="{id}", name="delete", methods={"DELETE"})
     */
    public function delete(
        Consignment $consignment,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        Security $security
    ): Response
    {
        if(!$security->isGranted(Permission::DELETE, $consignment)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
    
        foreach ($consignment->getConsignmentItems() as $consignmentItem) {
            $eventDispatcher->dispatch(new ChangeEvent(ChangeAction::delete(), $consignmentItem));
            $entityManager->remove($consignmentItem);
        }
        
        $eventDispatcher->dispatch(new ChangeEvent(ChangeAction::delete(), $consignment));
        $entityManager->remove($consignment);
        $entityManager->flush();
    
        return new Response(null, 204);
    }
    
    /**
     * @Route(path="addmultiple", name="addmultiple", methods={"POST"})
     * @ApiContext(groups={"detail"})
     */
    public function add_multiple(
        ConsignmentAddMultiple $consignmentAddMultiple,
        ConsignmentService $consignmentService,
        EntityManagerInterface $entityManager,
        Security $security
    ): iterable
    {
        if(!$security->isGranted(Permission::BOOK, Material::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
    
        $updatedMaterials = $consignmentService->addMultiplePositionsToConsignment($consignmentAddMultiple);
        
        $entityManager->flush();
        
        return $updatedMaterials;
    }
}
