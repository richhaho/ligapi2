<?php

namespace App\Controller;


use App\Api\ApiContext;
use App\Api\Dto\OrderSourceDto;
use App\Api\Mapper\OrderSourceMapper;
use App\Entity\Data\OrderStatus;
use App\Entity\Data\Permission;
use App\Entity\Material;
use App\Entity\OrderSource;
use App\Repository\OrderSourceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Security\Core\Security;

/**
 * @Route(path="/api/ordersources/", name="api_ordersource_")
 */
class OrderSourceController
{
    
    /**
     * @Route(name="index", methods={"GET"})
     * @ApiContext(groups={"shoppingCart"}, selfRoute="api_ordersource_get")
     */
    public function index(OrderSourceRepository $repository, Security $security, Request $request): iterable
    {
        if(!$security->isGranted(Permission::READ, Material::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        return $repository->getWithQuery($request->query->all());
    }
    
    /**
     * @Route(path="toorder", name="toorder", methods={"GET"})
     * @ApiContext(groups={"shoppingCart"}, selfRoute="api_ordersource_get")
     */
    public function toorder(OrderSourceRepository $orderSourceRepository): iterable
    {
        return $orderSourceRepository->getOrderSourcesOfMaterialWithOrderStatus(OrderStatus::toOrder());
    }
    
    /**
     * @Route(path="{id}", name="get", methods={"GET"})
     * @ApiContext(groups={"detail"}, selfRoute="api_ordersource_get")
     */
    public function get(OrderSource $orderSource, Security $security): OrderSource
    {
        if(!$security->isGranted(Permission::READ, $orderSource->getMaterial())) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        return $orderSource;
    }

    /**
    * @Route(path="{id}", name="put", methods={"PUT"})
    */
    public function put(
        OrderSourceDto $orderSourceDto,
        OrderSourceMapper $mapper,
        OrderSource $orderSource,
        EntityManagerInterface $entityManager,
        RequestContext $requestContext,
        Security $security
    ): OrderSource
    {
        if(!$security->isGranted(Permission::EDIT, $orderSource->getMaterial())) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        $requestContext->setParameter('changeSource', 'user');
        $orderSource = $mapper->putEntityFromDto($orderSourceDto, $orderSource);
        $entityManager->flush();
        return $orderSource;
    }
    
    /**
     * @Route(path="{id}", name="delete", methods={"DELETE"})
     */
    public function delete(OrderSource $orderSource, EntityManagerInterface $entityManager, Security $security): Response
    {
        if(!$security->isGranted(Permission::EDIT, $orderSource->getMaterial())) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        $entityManager->remove($orderSource);
        $entityManager->flush();
        
        return new Response(null, 204);
    }
    
}
