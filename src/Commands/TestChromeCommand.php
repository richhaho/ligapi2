<?php

declare(strict_types=1);


namespace App\Commands;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Panther\Client;

class TestChromeCommand extends Command
{
    public function __construct(
    )
    {
        parent::__construct();
    }
    
    protected function configure()
    {
        $this->setName('app:testchrome');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
            '==========',
            'Test start',
            '==========',
        ]);
    
        $client = Client::createChromeClient();
        $response = $client->request('GET', 'https://beispiel.de/');
        echo $response->html();
        
        return 0;
    }
    
}
