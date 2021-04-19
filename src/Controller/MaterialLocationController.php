<?php


namespace App\Controller;


use App\Api\ApiContext;
use App\Entity\Data\Permission;
use App\Entity\Material;
use App\Entity\MaterialLocation;
use App\Repository\MaterialLocationRepository;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route(path="/api/materiallocations/", name="api_materialLocation_")
 */
class MaterialLocationController
{
    
    /**
     * @Route(name="index", methods={"GET"})
     * @ApiContext(groups={"list"}, selfRoute="api_materialLocation_get")
     */
    public function index(MaterialLocationRepository $repository, Security $security): iterable
    {
        if(!$security->isGranted(Permission::READ, Material::class)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        return $repository->findAll();
    }
    
    /**
     * @Route(path="/{id}", name="get", methods={"GET"})
     * @ApiContext(groups={"detail"}, selfRoute="api_materialLocation_get")
     */
    public function get(MaterialLocation $materialLocation, Security $security): MaterialLocation
    {
        if(!$security->isGranted(Permission::READ, $materialLocation->getMaterial())) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        return $materialLocation;
    }
    
}
