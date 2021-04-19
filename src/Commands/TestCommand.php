<?php

declare(strict_types=1);


namespace App\Commands;


use App\Entity\User;
use App\Repository\MaterialRepository;
use App\Repository\UserRepository;
use App\Services\Import\ImportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\RequestContext;

class TestCommand extends Command
{
    
    private ImportService $importService;
    private string $publicPath;
    private RequestContext $requestContext;
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;
    private MaterialRepository $materialRepository;
    
    public function __construct(
        ImportService $importService,
        string $publicPath,
        MaterialRepository $materialRepository,
        RequestContext $requestContext,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    )
    {
        parent::__construct();
        $this->importService = $importService;
        $this->publicPath = $publicPath;
        $this->requestContext = $requestContext;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->materialRepository = $materialRepository;
    }
    
    protected function configure()
    {
        $this->setName('app:test');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
            'Test Start'
        ]);
    
        /** @var User $user */
        $user = $this->userRepository->findOneBy(['email' => 'logins@steffengrell.de']);
    
        $filter = $this->entityManager->getFilters()->getFilter('company');
        $filter->setParameter('company_id', $user->getCompany()->getId());
    
        $this->requestContext->setParameter('user', $user);
    
        $material = $this->materialRepository->findByAltScannerId('a');
        
        if ($material) {
            $output->writeln([
                $material->getId()
            ]);
            $output->writeln([
                implode(", ", $material->getAltScannerIds())
            ]);
        } else {
            $output->writeln([
                "Not found"
            ]);
        }
    
        $output->writeln([
            'Test End'
        ]);
        
        return 0;
    }
}
