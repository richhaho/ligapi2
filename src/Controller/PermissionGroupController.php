<?php

declare(strict_types=1);


namespace App\Controller;


use App\Api\ApiContext;
use App\Api\Dto\PermissionGroupDto;
use App\Api\Mapper\PermissionGroupMapper;
use App\Entity\Data\ChangeAction;
use App\Entity\Data\Permission;
use App\Entity\Material;
use App\Entity\PermissionGroup;
use App\Event\ChangeEvent;
use App\Repository\PermissionGroupRepository;
use App\Services\PermissionGroupService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @Route(path="/api/permissiongroups/", name="api_permission_group_")
 */
class PermissionGroupController
{
    /**
     * @Route(name="index", methods={"GET"})
     * @ApiContext(groups={"list"}, selfRoute="api_permission_group_get")
     */
    public function index(PermissionGroupRepository $repository): iterable
    {
//        if(!$security->isGranted(Permission::READ, PermissionGroup::PERMISSION)) {
//            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
//        }
        return $repository->findAll();
    }
    
    /**
     * @Route(path="{id}", name="get", methods={"GET"})
     * @ApiContext(groups={"detail"}, selfRoute="api_permission_group_get")
     */
    public function get(PermissionGroup $permissionGroup, Security $security): PermissionGroup
    {
        if(!$security->isGranted(Permission::READ, $permissionGroup)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        return $permissionGroup;
    }
    
    /**
     * @Route(name="create", methods={"POST"})
     * @ApiContext(groups={"list"}, selfRoute="api_permission_group_get")
     */
    public function create(
        PermissionGroupDto $createPermissionGroup,
        PermissionGroupMapper $mapper,
        EntityManagerInterface $em,
        Security $security
    ): PermissionGroup
    {
        if(!$security->isGranted(Permission::ADMIN, Material::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        $permissionGroup = $mapper->createPermissionGroupFromDto($createPermissionGroup);
        $em->persist($permissionGroup);
        $em->flush();
        
        return $permissionGroup;
    }
    
    /**
     * @Route(path="{id}", name="put", methods={"PUT"})
     */
    public function put(
        PermissionGroupDto $permissionGroupDto,
        PermissionGroupMapper $mapper,
        PermissionGroup $permissionGroup,
        EntityManagerInterface $em,
        Security $security
    ) : PermissionGroup
    {
        if(!$security->isGranted(Permission::ADMIN, Material::PERMISSION)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        $permissionGroup = $mapper->putPermissionGroupFromDto($permissionGroup, $permissionGroupDto);
        $em->flush();
        return $permissionGroup;
    }
    
    /**
     * @Route(path="{id}", name="delete", methods={"DELETE"})
     */
    public function delete(
        PermissionGroup $permissionGroup,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        Security $security,
        PermissionGroupService $permissionGroupService
    ): Response
    {
        if(!$security->isGranted(Permission::DELETE, $permissionGroup)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
    
        $permissionGroupService->removePermissionGroupFromRelatedEntities($permissionGroup);
        
        $entityManager->remove($permissionGroup);
        $eventDispatcher->dispatch(new ChangeEvent(ChangeAction::delete(), $permissionGroup));
        $entityManager->flush();
        
        return new Response(null, 204);
    }
}
