<?php

declare(strict_types=1);

namespace App\Controller;

use App\Api\ApiContext;
use App\Api\Dto\ItemGroupDto;
use App\Api\Mapper\ItemGroupMapper;
use App\Entity\Data\ChangeAction;
use App\Entity\Data\ItemGroupType;
use App\Entity\Data\Permission;
use App\Entity\ItemGroup;
use App\Entity\Material;
use App\Entity\Tool;
use App\Event\ChangeEvent;
use App\Exceptions\Domain\MissingDataException;
use App\Repository\ItemGroupRepository;
use App\Services\ItemGroupService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @Route(path="/api/itemgroups/", name="api_itemgroup_")
 */
class ItemGroupController
{
    /**
     * @Route(name="index", methods={"GET"})
     * @ApiContext(groups={"list"}, selfRoute="api_itemgroup_get")
     */
    public function index(Request $request, ItemGroupRepository $repository): iterable
    {
        $params = $request->query->all();
        return $repository->findWithParams($params);
    }
    
    /**
     * @Route(path="{id}", name="get", methods={"GET"})
     * @ApiContext(groups={"detail"}, selfRoute="api_itemgroup_get")
     */
    public function get(ItemGroup $itemGroup): ItemGroup
    {
        return $itemGroup;
    }
    
    /**
     * @Route(name="create", methods={"POST"})
     * @ApiContext(groups={"list"}, selfRoute="api_itemgroup_get")
     */
    public function create(
        ItemGroupDto $createItemGroup,
        ItemGroupMapper $mapper,
        EntityManagerInterface $em,
        Security $security
    ): ItemGroup
    {
        if ($createItemGroup->itemGroupType === ItemGroupType::material()->getValue()) {
            if(!$security->isGranted(Permission::EDIT, Material::PERMISSION)) {
                throw new AccessDeniedHttpException('Fehlende Berechtigung.');
            }
        } else if ($createItemGroup->itemGroupType === ItemGroupType::tool()->getValue()) {
            if(!$security->isGranted(Permission::EDIT, Tool::PERMISSION)) {
                throw new AccessDeniedHttpException('Fehlende Berechtigung.');
            }
        } else {
            throw MissingDataException::forMissingData('itemGroup type');
        }
        $itemGroup = $mapper->createItemGroup($createItemGroup);
        $em->persist($itemGroup);
        $em->flush();

        return $itemGroup;
    }
    
    /**
     * @Route(path="{id}", name="put", methods={"PUT"})
     */
    public function put(
        ItemGroupDto $itemGroupDto,
        ItemGroupMapper $mapper,
        ItemGroup $itemGroup,
        EntityManagerInterface $em) : ItemGroup
    {
        $itemGroup = $mapper->putItemGrouop($itemGroupDto, $itemGroup);
        $em->flush();
        return $itemGroup;
    }
    
    /**
     * @Route(path="{id}", name="delete", methods={"DELETE"})
     */
    public function delete(
        ItemGroup $itemGroup,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        Security $security,
        ItemGroupService $itemGroupService
    ): Response
    {
        if(!$security->isGranted(Permission::DELETE, $itemGroup)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        
        $itemGroupService->removeItemGroupFromRelatedEntities($itemGroup);
        
        $entityManager->remove($itemGroup);
        $eventDispatcher->dispatch(new ChangeEvent(ChangeAction::delete(), $itemGroup));
        $entityManager->flush();
    
        return new Response(null, 204);
    }
}
