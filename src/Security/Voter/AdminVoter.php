<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Data\Permission;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AdminVoter extends Voter
{
    protected function supports(string $attribute, $subject): bool
    {
        return
            in_array($attribute, Permission::levels());
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }
        
        if ($user->isAdmin() && !$user->isDeleted()) {
            return true;
        }

        return false;
    }
}
