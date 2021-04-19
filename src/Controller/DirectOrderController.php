<?php

declare(strict_types=1);

namespace App\Controller;

use App\Api\ApiContext;
use App\Api\Dto\DirectOrderDto;
use App\Api\Mapper\DirectOrderMapper;
use App\Entity\Data\ChangeAction;
use App\Entity\Data\Permission;
use App\Entity\Material;
use App\Entity\DirectOrder;
use App\Event\ChangeEvent;
use App\Repository\DirectOrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @Route(path="/api/directorders/", name="api_direct_order_")
 */
class DirectOrderController
{
    /**
     * @Route(name="index", methods={"GET"})
     * @ApiContext(groups={"list"}, selfRoute="api_direct_order_get")
     */
    public function index(DirectOrderRepository $repository, Security $security): iterable
    {
        if (!$security->isGranted(Permission::BOOK, Material::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        return $repository->findAll();
    }
    
    /**
     * @Route(path="{id}", name="get", methods={"GET"})
     * @ApiContext(groups={"directorderdetails"}, selfRoute="api_direct_order_get")
     */
    public function get(DirectOrder $directOrder, Security $security): DirectOrder
    {
        if (!$security->isGranted(Permission::READ, Material::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        return $directOrder;
    }
    
    /**
     * @Route(name="create", methods={"POST"})
     * @ApiContext(groups={"orderdetails"}, selfRoute="api_direct_order_get")
     */
    public function create(
        DirectOrderDto $directOrderDto,
        DirectOrderMapper $mapper,
        EntityManagerInterface $em,
        Security $security
    ): DirectOrder
    {
        if (!$security->isGranted(Permission::BOOK, Material::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        $directOrder = $mapper->createDirectOrderFromDto($directOrderDto);
        $em->persist($directOrder);
        $em->flush();
        
        return $directOrder;
    }
    
    /**
     * @Route(path="{id}", name="delete", methods={"DELETE"})
     */
    public function delete(
        DirectOrder $directOrder,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        Security $security
    ): Response
    {
        if (!$security->isGranted(Permission::DELETE, $directOrder)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        
        foreach ($directOrder->getDirectOrderPositions() as $directOrderPosition) {
            $entityManager->remove($directOrderPosition);
        }
        
        $entityManager->remove($directOrder);
        $eventDispatcher->dispatch(new ChangeEvent(ChangeAction::delete(), $directOrder));
        $entityManager->flush();
        
        return new Response(null, 204);
    }
}
