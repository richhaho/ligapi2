<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Data\Permission;
use App\Entity\PermissionAwareInterface;
use App\Entity\Task;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class TaskPermissionVoter extends Voter
{
    private ClassPermissionVoter $classPermissionVoter;

    public function __construct(ClassPermissionVoter $classPermissionVoter)
    {
        $this->classPermissionVoter = $classPermissionVoter;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof Task;
    }
    
    /**
     * @param Task $subject
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
        
        if ($this->classPermissionVoter->vote($token, $subject, [$attribute]) === VoterInterface::ACCESS_GRANTED) {
            return true;
        }

        return $subject->getResponsible() === $user->getFullName();
    }
}
