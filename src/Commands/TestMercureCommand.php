<?php

declare(strict_types=1);


namespace App\Commands;


use App\Services\MercureService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestMercureCommand extends Command
{
    private MercureService $mercureService;
    
    public function __construct(
        MercureService $mercureService
    )
    {
        parent::__construct();
        $this->mercureService = $mercureService;
    }
    
    protected function configure()
    {
        $this->setName('app:mer');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
            '==========',
            'Test start',
            '==========',
        ]);
        
        $result = $this->mercureService->sendMessage('materials', ['id' => '4c28e00e-998f-4489-9402-761113d9f2c7']);
    
        $output->writeln([
            '==========',
            $result,
            'Test End',
            '==========',
        ]);
        
        return 0;
    }
    
}
