<?php

declare(strict_types=1);


namespace App\Controller;


use App\Api\ApiContext;
use App\Entity\Data\Permission;
use App\Entity\Material;
use App\Entity\StockChange;
use App\Repository\StockChangeRepository;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route(path="/api/stockchanges/", name="api_stockchange_")
 */
class StockChangeController
{
    /**
     * @Route(name="index", methods={"GET"})
     * @ApiContext(groups={"listStockChanges"}, selfRoute="api_stockchange_get")
     */
    public function index(StockChangeRepository $repository, Security $security): iterable
    {
        if(!$security->isGranted(Permission::READ, Material::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        return $repository->findBy([], ['createdAt' => 'DESC']);
    }
    
    /**
     * @Route(path="/{id}", name="get", methods={"GET"})
     * @ApiContext(groups={"detail"}, selfRoute="api_stockchange_get")
     */
    public function get(StockChange $stockChange, Security $security): StockChange
    {
        if(!$security->isGranted(Permission::READ, $stockChange->getMaterialLocation()->getMaterial())) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        return $stockChange;
    }
}
