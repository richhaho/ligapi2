<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Data\Permission;
use App\Entity\PermissionAwareInterface;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class EntityPermissionVoter extends Voter
{
    private ClassPermissionVoter $classPermissionVoter;

    public function __construct(ClassPermissionVoter $classPermissionVoter)
    {
        $this->classPermissionVoter = $classPermissionVoter;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return
            in_array($attribute, Permission::levels()) &&
            is_object($subject) &&
            $subject instanceof PermissionAwareInterface;
    }
    
    /**
     * @param PermissionAwareInterface $subject
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }
        
        if ($subject->getCompany()->getId() !== $user->getCompany()->getId()) {
            return false;
        }

        $permissionType = $subject->getPermissionType();

        if ($this->classPermissionVoter->vote($token, $permissionType, [$attribute]) === VoterInterface::ACCESS_GRANTED) {
            return true;
        }

        /** @var Permission $permission */
        foreach ($user->getPermissions() as $permission) {
            if ($subject->getPermissionGroup() && $permission->appliesToCategory($subject->getPermissionGroup()->getId()) && $permission->includes($attribute)) {
                return true;
            }
        }

        return false;
    }
}
