<?php

declare(strict_types=1);


namespace App\Api\Mapper;


use App\Api\Dto\PermissionGroupDto;
use App\Entity\Data\Permission;
use App\Entity\PermissionGroup;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Services\CurrentUserProvider;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PermissionGroupMapper
{
    use ValidationTrait;
    
    
    private CurrentUserProvider $currentUserProvider;
    private ValidatorInterface $validator;
    private UserRepository $userRepository;
    
    public function __construct(
        CurrentUserProvider $currentUserProvider,
        ValidatorInterface $validator,
        UserRepository $userRepository
    )
    {
        $this->currentUserProvider = $currentUserProvider;
        $this->validator = $validator;
        $this->userRepository = $userRepository;
    }
    
    public function createPermissionGroupFromDto(PermissionGroupDto $permissionGroupDto): PermissionGroup
    {
        $this->validate($permissionGroupDto);
        
        $currentUser = $this->currentUserProvider->getAuthenticatedUser();
        
        $permission = new PermissionGroup($permissionGroupDto->name, $currentUser->getCompany());
        
        $users = $this->userRepository->findAll();
    
        /** @var User $user */
        foreach ($users as $user) {
            $permissions = [];
            foreach ($user->getPermissions() as $perm) {
                $permissions[] = $perm;
            }
            $permissions[] = Permission::fromArray([
                'action' => 'NONE',
                'category' => $permission->getId()
            ]);
            $user->setPermissions($permissions);
        }
    
        return $permission;
    }
    
    public function putPermissionGroupFromDto(PermissionGroup $permissionGroup, PermissionGroupDto $permissionGroupDto): PermissionGroup
    {
        $permissionGroupDto->id =$permissionGroup->getId();
        
        $this->validate($permissionGroupDto);
        
        $permissionGroup->setName($permissionGroupDto->name);
        
        return $permissionGroup;
    }
}
