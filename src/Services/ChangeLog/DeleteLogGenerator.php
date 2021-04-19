<?php

declare(strict_types=1);


namespace App\Services\ChangeLog;


use App\Entity\ChangeLog;
use App\Entity\Data\ChangeAction;
use App\Services\CurrentUserProvider;

class DeleteLogGenerator implements ChangeLogGeneratorInterface
{
    
    private CurrentUserProvider $currentUserProvider;
    
    public function __construct(CurrentUserProvider $currentUserProvider)
    {
        $this->currentUserProvider = $currentUserProvider;
    }
    
    public function supports(string $action, object $entity): bool
    {
        return 'delete' === $action;
    }
    
    public function getChangeLogs(object $entity): iterable
    {
        $class = get_class($entity);
        $currentUser = $this->currentUserProvider->getAuthenticatedUser();
        $change = new ChangeLog(
            $currentUser->getCompany()->getId(),
            $currentUser->getId(),
            $class,
            ChangeAction::delete(),
            $entity->getId()
        );
        return [$change];
    }
}
