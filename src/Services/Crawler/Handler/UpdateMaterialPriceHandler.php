<?php

declare(strict_types=1);


namespace App\Services\Crawler\Handler;


use App\Services\Crawler\Crawler;
use App\Services\Crawler\Messages\UpdatePrices;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class UpdateMaterialPriceHandler implements MessageHandlerInterface
{
    private Crawler $crawler;
    private EntityManagerInterface $entityManager;
    
    public function __construct(
        Crawler $crawler,
        EntityManagerInterface $entityManager
    )
    {
        $this->crawler = $crawler;
        $this->entityManager = $entityManager;
    }
    
    public function __invoke(UpdatePrices $updatePrices)
    {
        $companyFilterWasEnabled = false;
        if ($this->entityManager->getFilters()->isEnabled('company')) {
            $companyFilterWasEnabled = true;
            $this->entityManager->getFilters()->disable('company');
        }
        $this->crawler->updatePriceForOrderSource($updatePrices->getOrderSourceIds());
        if ($companyFilterWasEnabled) {
            $this->entityManager->getFilters()->enable('company');
        }
    }
}
