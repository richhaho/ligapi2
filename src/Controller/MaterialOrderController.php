<?php

declare(strict_types=1);

namespace App\Controller;

use App\Api\ApiContext;
use App\Api\Dto\MaterialOrderDto;
use App\Api\Mapper\MaterialOrderMapper;
use App\Entity\Data\ChangeAction;
use App\Entity\Data\Permission;
use App\Entity\Material;
use App\Entity\MaterialOrder;
use App\Event\ChangeEvent;
use App\Repository\MaterialOrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @Route(path="/api/materialorders/", name="api_material_order_")
 */
class MaterialOrderController
{
    /**
     * @Route(name="index", methods={"GET"})
     * @ApiContext(groups={"list"}, selfRoute="api_material_order_get")
     */
    public function index(MaterialOrderRepository $repository, Security $security): iterable
    {
        if (!$security->isGranted(Permission::BOOK, Material::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        return $repository->findAll();
    }
    
    /**
     * @Route(path="{id}", name="get", methods={"GET"})
     * @ApiContext(groups={"orderdetails"}, selfRoute="api_material_order_get")
     */
    public function get(MaterialOrder $materialOrder, Security $security): MaterialOrder
    {
        if (!$security->isGranted(Permission::BOOK, Material::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        return $materialOrder;
    }
    
    /**
     * @Route(name="create", methods={"POST"})
     * @ApiContext(groups={"orderdetails"}, selfRoute="api_material_order_get")
     */
    public function create(
        MaterialOrderDto $materialOrderDto,
        MaterialOrderMapper $mapper,
        EntityManagerInterface $em,
        Security $security
    ): MaterialOrder
    {
        if (!$security->isGranted(Permission::BOOK, Material::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        $materialOrder = $mapper->createMaterialOrderFromDto($materialOrderDto);
        $em->persist($materialOrder);
        $em->flush();
        
        return $materialOrder;
    }
    
    /**
     * @Route(path="{id}", name="delete", methods={"DELETE"})
     */
    public function delete(
        MaterialOrder $materialOrder,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        Security $security
    ): Response
    {
        if (!$security->isGranted(Permission::DELETE, $materialOrder)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        
        foreach ($materialOrder->getMaterialOrderPositions() as $materialOrderPosition) {
            $entityManager->remove($materialOrderPosition);
        }
        
        $entityManager->remove($materialOrder);
        $eventDispatcher->dispatch(new ChangeEvent(ChangeAction::delete(), $materialOrder));
        $entityManager->flush();
        
        return new Response(null, 204);
    }
}
