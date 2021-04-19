<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Services\CurrentUserProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class CompanyFilterListener implements EventSubscriberInterface
{
    private EntityManagerInterface $manager;

    private CurrentUserProvider $userProvider;

    public function __construct(
        EntityManagerInterface $manager,
        CurrentUserProvider $userProvider
    )
    {
        $this->manager = $manager;
        $this->userProvider = $userProvider;
    }

    public function onRequest(RequestEvent $e): void
    {
        if (!$e->isMasterRequest()) {
            return;
        }

        $filter = $this->manager->getFilters()->getFilter('company');
        $currentUser = $this->userProvider->getAuthenticatedUser();
        if ($currentUser) {
            $companyId = $this->userProvider->getCompany()->getId();
            $filter->setParameter('company_id', $companyId);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => 'onRequest'
        ];
    }
}
