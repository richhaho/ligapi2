<?php

declare(strict_types=1);


namespace App\Api\Mapper;


use App\Api\Dto\CreateUser;
use App\Api\Dto\DtoInterface;
use App\Api\Dto\PutUser;
use App\Entity\Consignment;
use App\Entity\Data\Permission;
use App\Entity\User;
use App\Exceptions\Domain\MissingDataException;
use App\Exceptions\Domain\UnsupportedMethodException;
use App\Repository\PermissionGroupRepository;
use App\Repository\UserRepository;
use App\Security\UserService;
use App\Services\CurrentUserProvider;
use App\Services\ItemNumberService;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserMapper implements MapperInterface
{
    use ValidationTrait;
    
    private CurrentUserProvider $currentUserProvider;
    private UserService $userService;
    private UserPasswordEncoderInterface $encoder;
    private ValidatorInterface $validator;
    private EntityManagerInterface $entityManager;
    private PermissionGroupRepository $permissionGroupRepository;
    private ItemNumberService $itemNumberService;
    private UserRepository $userRepository;
    private RequestContext $requestContext;
    
    public function __construct(
        CurrentUserProvider $currentUserProvider,
        UserService $userService,
        UserPasswordEncoderInterface $encoder,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager,
        ItemNumberService $itemNumberService,
        PermissionGroupRepository $permissionGroupRepository,
        UserRepository $userRepository,
        RequestContext $requestContext
    )
    {
        $this->currentUserProvider = $currentUserProvider;
        $this->userService = $userService;
        $this->encoder = $encoder;
        $this->validator = $validator;
        $this->entityManager = $entityManager;
        $this->permissionGroupRepository = $permissionGroupRepository;
        $this->itemNumberService = $itemNumberService;
        $this->userRepository = $userRepository;
        $this->requestContext = $requestContext;
    }
    
    private function ensureUserHasAllPermissionGroups(User $user): User
    {
        $companyFilterWasEnabled = false; // TODO: make it work without this hack. Problem occurs if user is created.
        if ($this->entityManager->getFilters()->isEnabled('company')) {
            $companyFilterWasEnabled = true;
            $this->entityManager->getFilters()->disable('company');
        }
        $permissionGroups = $this->permissionGroupRepository->findBy(['company' => $user->getCompany()]);
        if ($companyFilterWasEnabled) {
            $this->entityManager->getFilters()->enable('company');
        }
        
        $userPermissions = $user->getPermissions();
        
        foreach ($permissionGroups as $permissionGroup) {
            $userHasPermissionGroup = false;
            /** @var Permission $permission */
            foreach ($userPermissions as $permission) {
                if ($permissionGroup->getId() === $permission->getCategory()) {
                    $userHasPermissionGroup = true;
                }
            }
            if (!$userHasPermissionGroup) {
                $userPermissions[] = Permission::fromArray([
                    "category" => $permissionGroup->getId(),
                    "action" => Permission::NONE
                ]);
            }
        }
        
        $user->setPermissions($userPermissions);
        
        return $user;
    }
    
    public function supports(string $dtoName): bool
    {
        return $dtoName === CreateUser::class || $dtoName === PutUser::class;
    }
    
    /**
     * @param CreateUser $dto
     */
    public function createEntityFromDto(DtoInterface $dto, string $userId): User
    {
        /** @var User $user */
        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw MissingDataException::forEntityNotFound($userId, User::class);
        }
        $this->requestContext->setParameter('user', $user);
        
        $this->validate($dto);
        
        $currentUser = $this->currentUserProvider->getAuthenticatedUser();
        
        $user = $this->userService->createUser($dto->firstName, $dto->lastName, $dto->email, $dto->password, $currentUser->getCompany());
        
        $user->setIsAdmin($dto->isAdmin);
        $permissions = [];
        foreach ($dto->permissions as $permission) {
            $permission = new Permission($permission['category'], $permission['action']);
            $permissions[] = $permission;
        }
        $user->setPermissions($permissions);
    
        $consignmentNumber = $this->itemNumberService->getNextItemNumber(Consignment::class, $user->getCompany());
        $userConsignment = new Consignment($currentUser->getCompany(), $consignmentNumber, null, $user);
        
        $user = $this->ensureUserHasAllPermissionGroups($user);
        
        $this->entityManager->persist($userConsignment);
        
        return $user;
    }
    
    /**
     * @param PutUser $dto
     * @param User $entity
     */
    public function putEntityFromDto(DtoInterface $dto, object $entity): User
    {
        $dto->id = $entity->getId();
        $this->validate($dto);
        
        if ($dto->password) {
            $entity->updatePassword($dto->password, $this->encoder);
        }
        
        $entity->setFirstName($dto->firstName);
        $entity->setLastName($dto->lastName);
        $entity->setEmail($dto->email);
        
        if ($dto->isAdmin !== null) {
            $entity->setIsAdmin($dto->isAdmin);
        }
        
        if ($dto->permissions !== null) {
            $permissions = [];
            foreach ($dto->permissions as $permission) {
                $permission = new Permission($permission['category'], $permission['action']);
                $permissions[] = $permission;
            }
            $entity->setPermissions($permissions);
        }
    
        $entity = $this->ensureUserHasAllPermissionGroups($entity);
        
        return $entity;
    }
    
    public function patchEntityFromDto(DtoInterface $dto, object $entity)
    {
        throw UnsupportedMethodException::forUnsupportedMethod('User Mapper patchEntityFromDto');
    }
    
    /**
     * @param CreateUser $dto
     */
    public function prepareImport(DtoInterface $dto, string $userId): ?CreateUser
    {
        /** @var User $user */
        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw MissingDataException::forEntityNotFound($userId, User::class);
        }
        $this->requestContext->setParameter('user', $user);
        
        $alreadyCreatedUser = $this->userRepository->findByFullName($dto->firstName . ' ' . $dto->lastName);
        
        if ($alreadyCreatedUser) {
            return null;
        }
        
        $dto->password = Uuid::uuid4()->toString();
    
        $companyFilterWasEnabled = false; // TODO: make it work without this hack. Problem occurs if user is created.
        if ($this->entityManager->getFilters()->isEnabled('company')) {
            $companyFilterWasEnabled = true;
            $this->entityManager->getFilters()->disable('company');
        }
        $permissionGroups = $this->permissionGroupRepository->findBy(['company' => $user->getCompany()]);
        if ($companyFilterWasEnabled) {
            $this->entityManager->getFilters()->enable('company');
        }
        
        $permissionAccess = [];
        foreach ($permissionGroups as $permissionGroup) {
            $permissionAccess[] = [
                "category" => $permissionGroup->getId(),
                "action" => Permission::NONE
            ];
        }
        $dto->permissions = $permissionAccess;
        return $dto;
    }
}
