<?php

declare(strict_types=1);


namespace App\Commands;


use App\Api\Dto\CreateMaterialDto;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Services\Import\ImportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\RequestContext;

class TestImportCommand extends Command
{
    
    private ImportService $importService;
    private string $publicPath;
    private RequestContext $requestContext;
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;
    
    public function __construct(
        ImportService $importService,
        string $publicPath,
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
    }
    
    protected function configure()
    {
        $this->setName('app:import');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
            'Import Start'
        ]);
    
        /** @var User $user */
        $user = $this->userRepository->findOneBy(['email' => 'logins@steffengrell.de']);
    
        $filter = $this->entityManager->getFilters()->getFilter('company');
        $filter->setParameter('company_id', $user->getCompany()->getId());
    
        $this->requestContext->setParameter('user', $user);
    
        $file = new UploadedFile($this->publicPath . '/importtest.xlsx', 'importtest.xlsx');
    
        $this->importService->importFile($file, CreateMaterialDto::class);
    
        $output->writeln([
            'Import End'
        ]);
        
        return 0;
    }
}
