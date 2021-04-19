<?php

declare(strict_types=1);


namespace App\Commands;


use App\Services\OneSignalService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Panther\Client;

class TestPushCommand extends Command
{
    private OneSignalService $oneSignalService;
    
    public function __construct(
        OneSignalService $oneSignalService
    )
    {
        parent::__construct();
        $this->oneSignalService = $oneSignalService;
    }
    
    protected function configure()
    {
        $this->setName('app:push');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
            '==========',
            'Test start',
            '==========',
        ]);
        
        $recipients = ['997E1CEC-DC33-4916-B407-0265A6720F51'];
    
        $this->oneSignalService->sendForgroundMessage('test', 'Es funktioniert!','Details', $recipients);
        
        return 0;
    }
    
}
