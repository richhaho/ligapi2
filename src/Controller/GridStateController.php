<?php

declare(strict_types=1);


namespace App\Controller;


use App\Api\ApiContext;
use App\Api\Dto\GridStateDto;
use App\Api\Mapper\GridStateMapper;
use App\Entity\Data\ChangeAction;
use App\Entity\GridState;
use App\Event\ChangeEvent;
use App\Repository\GridStateRepository;
use App\Services\CurrentUserProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @Route(path="/api/gridstates/", name="api_gridstate_")
 */
class GridStateController
{
    /**
     * @Route(name="index", methods={"GET"})
     * @ApiContext(groups={"list"}, selfRoute="api_gridstate_get")
     */
    public function index(GridStateRepository $repository): iterable
    {
//        if(!$security->isGranted(Permission::READ, GridState::class)) {
//            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
//        }
        return $repository->getNonDefaultGridStates();
    }

    /**
     * @Route(path="default/{type}", name="get_default", methods={"GET"})
     * @ApiContext(groups={"detail"}, selfRoute="api_gridstate_get")
     */
    public function get_default(string $type, GridStateRepository $gridStateRepository, CurrentUserProvider $currentUserProvider): ?GridState
    {
        return $gridStateRepository->findDefaultByTypeAndUser($type, $currentUserProvider->getAuthenticatedUser());
    }
    
    /**
     * @Route(path="{id}", name="get", methods={"GET"})
     * @ApiContext(groups={"detail"}, selfRoute="api_gridstate_get")
     */
    public function get(GridState $gridState): GridState
    {
//        if(!$security->isGranted(Permission::READ, $gridState)) {
//            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
//        }
        return $gridState;
    }
    
    /**
     * @Route(name="create", methods={"POST"})
     * @ApiContext(groups={"list"}, selfRoute="api_gridstate_get")
     */
    public function create(
        GridStateDto $gridStateDto,
        GridStateMapper $mapper,
        EntityManagerInterface $em
    ): GridState
    {
//        if(!$security->isGranted(Permission::CREATE, GridState::class)) {
//            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
//        }
        $gridState = $mapper->createGridStateFromDto($gridStateDto);
        $em->persist($gridState);
        $em->flush();
        
        return $gridState;
    }
    
    /**
     * @Route(path="{id}", name="put", methods={"PUT"})
     */
    public function put(
        GridStateDto $gridStateDto,
        GridStateMapper $mapper,
        GridState $gridState,
        EntityManagerInterface $em
    ) : GridState
    {
//        if(!$security->isGranted(Permission::EDIT, GridState::class)) {
//            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
//        }
        $gridState = $mapper->updateGridStateFromDto($gridState, $gridStateDto);
        $em->flush();
        return $gridState;
    }
    
    /**
     * @Route(path="{id}", name="delete", methods={"DELETE"})
     */
    public function delete(
        GridState $gridState,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher
    ): Response
    {
//        if(!$security->isGranted(Permission::DELETE, $gridState)) {
//            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
//        }
        
        $entityManager->remove($gridState);
        $eventDispatcher->dispatch(new ChangeEvent(ChangeAction::delete(), $gridState));
        $entityManager->flush();
        
        return new Response(null, 204);
    }
}
