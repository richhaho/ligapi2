<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Data\Permission;
use App\Entity\Keyy;
use App\Entity\Material;
use App\Entity\Project;
use App\Entity\Tool;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ClassPermissionVoter extends Voter
{
    private AdminVoter $adminVoter;
    
    public function __construct(AdminVoter $adminVoter)
    {
        $this->adminVoter = $adminVoter;
    }
    
    protected function supports(string $attribute, $subject): bool
    {
        $permissionClasses = [Material::PERMISSION, Tool::PERMISSION, Keyy::PERMISSION, Project::PERMISSION];
        
        return
            in_array($attribute, Permission::levels()) &&
            is_string($subject) &&
            in_array($subject, $permissionClasses);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User || $user->isDeleted()) {
            return false;
        }
    
        if ($this->adminVoter->vote($token, $subject, [$attribute]) === VoterInterface::ACCESS_GRANTED) {
            return true;
        }

        /** @var Permission $permission */
        foreach ($user->getPermissions() as $permission) {
            if ($permission->appliesToCategory($subject) && $permission->includes($attribute)) {
                return true;
            }
        }

        return false;
    }
}
