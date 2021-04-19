<?php

declare(strict_types=1);

namespace App\Controller;

use App\Api\ApiContext;
use App\Api\Dto\ActionDto;
use App\Api\Dto\LabelSelectDto;
use App\Api\Dto\PushIdDto;
use App\Api\Dto\PutUser;
use App\Entity\ChangeLog;
use App\Entity\Data\ChangeAction;
use App\Entity\Data\Permission;
use App\Entity\PdfDocumentType;
use App\Event\ChangeEvent;
use App\Exceptions\Domain\MissingDataException;
use App\Repository\PdfDocumentTypeRepository;
use App\Services\CurrentUserProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Api\Mapper\UserMapper;
use App\Api\Dto\CreateUser;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @Route(path="/api/users/", name="api_user_")
 */
class UserController
{
    /**
     * @Route(path="own", name="get_own", methods={"GET"})
     * @ApiContext(groups={"permission"})
     *
     */
    public function get_own(CurrentUserProvider $currentUserProvider): User
    {
        return $currentUserProvider->getAuthenticatedUser();
    }
    
    /**
     * @Route(path="setownselectedlabel", name="set_own_selected_label", methods={"POST"})
     */
    public function set_own_selected_label(
        CurrentUserProvider $currentUserProvider,
        LabelSelectDto $labelSelectDto,
        PdfDocumentTypeRepository $labelTypeRepository,
        EntityManagerInterface $entityManager
    ): Response
    {
        $user = $currentUserProvider->getAuthenticatedUser();
        $labelType = $labelTypeRepository->find($labelSelectDto->labelTypeId);
        if (!$labelType) {
            throw MissingDataException::forEntityNotFound($labelSelectDto->labelTypeId, PdfDocumentType::class);
        }
        switch ($labelSelectDto->entityType) {
            case "material":
                $user->setSelectedMaterialLabelType($labelType);
                break;
            case "tool":
                $user->setSelectedToolLabelType($labelType);
                break;
            case "keyy":
                $user->setSelectedKeyyLabelType($labelType);
                break;
        }
        $entityManager->flush();
        return new Response(null, 204);
    }
    
    /**
     * @Route(path="setpushid", name="set_push_id", methods={"POST"})
     */
    public function set_push_id(PushIdDto $idDto, CurrentUserProvider $currentUserProvider, EntityManagerInterface $entityManager): Response
    {
        $user = $currentUserProvider->getAuthenticatedUser();
        
        if ($idDto->type === 'mobile') {
            $user->setMobilePushId($idDto->id);
        }
        if ($idDto->type === 'web') {
            $user->setWebPushId($idDto->id);
        }
        
        $entityManager->flush();
    
        return new Response(null, 204);
    }
    
    /**
     * @Route(name="index", methods={"GET"})
     * @ApiContext(groups={"list"}, selfRoute="api_user_get")
     */
    public function index(UserRepository $repository, Security $security, CurrentUserProvider $currentUserProvider, EntityManagerInterface $entityManager): iterable
    {
        if(!$security->isGranted(Permission::ADMIN, User::class)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        if ($currentUserProvider->getAuthenticatedUser()->getDeviceUuid()) {
            throw new BadRequestHttpException('Diese Funktion steht bei der mobilen Anmeldung nicht zur Verfügung.');
        }
    
        $entityManager->getFilters()->enable('deleted');
        
        return $repository->findAll();
    }
    
    /**
     * @Route(path="{id}", name="get", methods={"GET"})
     * @ApiContext(groups={"detail"}, selfRoute="api_user_get")
     */
    public function get(User $user, Security $security): User
    {
        if(!$security->isGranted(Permission::ADMIN, $user)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        return $user;
    }
    
    /**
     * @Route(name="create", methods={"POST"})
     * @ApiContext(groups={"detail"}, selfRoute="api_user_get")
     */
    public function create(
        CreateUser $createUser,
        UserMapper $mapper,
        EntityManagerInterface $entityManager,
        Security $security,
        CurrentUserProvider $currentUserProvider
    ): User
    {
        if(!$security->isGranted(Permission::ADMIN, User::class)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        if ($currentUserProvider->getAuthenticatedUser()->getDeviceUuid()) {
            throw new BadRequestHttpException('Diese Funktion steht bei der mobilen Anmeldung nicht zur Verfügung.');
        }
        $user = $mapper->createEntityFromDto($createUser, $currentUserProvider->getAuthenticatedUser()->getId());
        $entityManager->persist($user);
        $entityManager->flush();
    
        return $user;
    }
    
    /**
     * @Route(path="{id}", name="put", methods={"PUT"})
     */
    public function put(
        PutUser $putUser,
        UserMapper $mapper,
        User $user,
        EntityManagerInterface $entityManager,
        Security $security,
        CurrentUserProvider $currentUserProvider
    ) : User
    {
        if(!$security->isGranted(Permission::ADMIN, $user)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        if ($currentUserProvider->getAuthenticatedUser()->getDeviceUuid()) {
            throw new BadRequestHttpException('Diese Funktion steht bei der mobilen Anmeldung nicht zur Verfügung.');
        }
        $user = $mapper->putEntityFromDto($putUser, $user);
        $entityManager->flush();
        return $user;
    }
    
    /**
     * @Route(path="{id}", name="delete", methods={"DELETE"})
     */
    public function delete(
        User $user,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        Security $security,
        CurrentUserProvider $currentUserProvider
    ): Response
    {
        if(!$security->isGranted(Permission::ADMIN, $user)) {
            throw new AccessDeniedHttpException('Fehlende Berechtigung.');
        }
        if ($currentUserProvider->getAuthenticatedUser()->getDeviceUuid()) {
            throw new AccessDeniedHttpException('Diese Funktion steht bei der mobilen Anmeldung nicht zur Verfügung.');
        }
        $eventDispatcher->dispatch(new ChangeEvent(ChangeAction::delete(), $user));
        
        $user->setDeleted(true);
        
        $entityManager->flush();
    
        return new Response(null, 204);
    }
    
    /**
     * @Route(path="{id}/actionlog", name="log_action", methods={"POST"})
     */
    public function log_action(ActionDto $actionDto, CurrentUserProvider $currentUserProvider, EntityManagerInterface $entityManager): Response
    {
        $currentUser = $currentUserProvider->getAuthenticatedUser();
        $actionLog = new ChangeLog(
            $currentUser->getCompany()->getId(),
            $currentUser->getId(),
            $actionDto->page,
            ChangeAction::app(),
            $actionDto->action
        );
        $entityManager->persist($actionLog);
        $entityManager->flush();
        return new Response(null, 204);
    }
}
