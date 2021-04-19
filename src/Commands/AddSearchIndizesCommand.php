<?php

declare(strict_types=1);


namespace App\Commands;


use App\Entity\Data\ChangeAction;
use App\Repository\MaterialRepository;
use App\Services\SearchService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddSearchIndizesCommand extends Command
{
    private SearchService $searchService;
    private MaterialRepository $materialRepository;
    private EntityManagerInterface $entityManager;
    
    public function __construct(
        SearchService $searchService,
        MaterialRepository $materialRepository,
        EntityManagerInterface $entityManager
    )
    {
        parent::__construct();
        $this->searchService = $searchService;
        $this->materialRepository = $materialRepository;
        $this->entityManager = $entityManager;
    }
    
    protected function configure()
    {
        $this->setName('app:search');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            '==========',
            'Search index start',
            '==========',
        ]);
        
        $materials = $this->materialRepository->findAll();
    
        foreach ($materials as $material) {
            $this->searchService->addToSearchindex($material, ChangeAction::create());
        }
    
        $this->entityManager->flush();
        
        $output->writeln([
            '==========',
            'Search index end',
            '==========',
        ]);
        
        return 0;
    }
    
}
