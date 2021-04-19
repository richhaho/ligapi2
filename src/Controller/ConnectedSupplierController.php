<?php

declare(strict_types=1);


namespace App\Controller;


use App\Api\ApiContext;
use App\Entity\ConnectedSupplier;
use App\Entity\Data\Permission;
use App\Entity\Material;
use App\Repository\ConnectedSupplierRepository;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route(path="/api/connectedsuppliers/", name="api_connectedsupplier_")
 */
class ConnectedSupplierController
{
    /**
     * @Route(name="index", methods={"GET"})
     * @ApiContext(groups={"list"}, selfRoute="api_supplier_get")
     */
    public function index(ConnectedSupplierRepository $repository, Security $security): iterable
    {
        if(!$security->isGranted(Permission::READ, Material::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        return $repository->findAll();
    }
}
