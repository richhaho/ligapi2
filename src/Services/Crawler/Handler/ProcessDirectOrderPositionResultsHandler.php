<?php

declare(strict_types=1);


namespace App\Services\Crawler\Handler;


use App\Services\Crawler\Crawler;
use App\Services\Crawler\Messages\ProcessDirectOrderPositionResults;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ProcessDirectOrderPositionResultsHandler implements MessageHandlerInterface
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
    
    public function __invoke(ProcessDirectOrderPositionResults $processDirectOrderPositionResults)
    {
        $companyFilterWasEnabled = false;
        if ($this->entityManager->getFilters()->isEnabled('company')) {
            $companyFilterWasEnabled = true;
            $this->entityManager->getFilters()->disable('company');
        }
        $this->crawler->processDirectOrderPositionResults($processDirectOrderPositionResults->getDirectOrderPositionResultIds());
        if ($companyFilterWasEnabled) {
            $this->entityManager->getFilters()->enable('company');
        }
    }
}
