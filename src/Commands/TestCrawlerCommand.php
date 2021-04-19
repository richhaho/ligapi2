<?php

declare(strict_types=1);


namespace App\Commands;


use App\Services\Crawler\CrawlerTestServices;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class TestCrawlerCommand extends Command
{
    
    private CrawlerTestServices $crawlerTests;
    
    public function __construct(
        CrawlerTestServices $crawlerTests
    )
    {
        parent::__construct();
        $this->crawlerTests = $crawlerTests;
    }
    
    protected function configure()
    {
        $this->setName('app:crawler');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
            '==========',
            'Test start',
            '==========',
        ]);
    
        $helper = $this->getHelper('question');
    
        $supplerQuestion = new ChoiceQuestion(
            'Please select supplier to test',
            // choices can also be PHP objects that implement __toString() method
            ['GC', 'PM', 'Reisser', 'SR', 'Lotter'],
            0
        );
        $supplerQuestion->setErrorMessage('Supplier %s is invalid.');
    
        $typeQuestion = new ChoiceQuestion(
            'Please select crawler to test',
            // choices can also be PHP objects that implement __toString() method
            ['Material Data', 'Availability Data', 'Order', 'Price Update'],
            0
        );
        $typeQuestion->setErrorMessage('Supplier %s is invalid.');
        
        $supplierToTest = $helper->ask($input, $output, $supplerQuestion);
        $crawlerToTest = $helper->ask($input, $output, $typeQuestion);
        
        $output->writeln(['Data loading...']);
        
        $result = '';
        
        switch ($crawlerToTest) {
            case 'Material Data':
                $materialCrawlerDto = $this->crawlerTests->testGetMaterialData($supplierToTest);
                $result = [
                    'purchasing price' . $materialCrawlerDto->orderSources[0]->price,
                    'order number' . $materialCrawlerDto->orderSources[0]->orderNumber,
                    'amount per purchasing unit' . $materialCrawlerDto->orderSources[0]->amountPerPurchaseUnit,
                    'name' . $materialCrawlerDto->name,
                    'imgFile' . $materialCrawlerDto->imgFile->getPath(),
                    'manufacturerName' . $materialCrawlerDto->manufacturerName,
                    'manufacturerNumber' . $materialCrawlerDto->manufacturerNumber,
                    'note' . $materialCrawlerDto->note,
                    'sellingPrice' . $materialCrawlerDto->sellingPrice,
                    'unit' . $materialCrawlerDto->unit
                ];
                break;
            case 'Availability Data':
                $availabilityDto = $this->crawlerTests->testGetAvailabilityInfos($supplierToTest);
                $result = [
                    'price: ' . $availabilityDto->getPrice()->getAmount(),
                    'availability: ' . $availabilityDto->getAvailability()
                ];
                break;
            case 'Order':
                $result = json_encode($this->crawlerTests->testGetMaterialData($supplierToTest));
                break;
            case 'Price Update':
                $result = json_encode($this->crawlerTests->testGetMaterialData($supplierToTest));
                break;
        }
    
        $output->writeln(array_merge(
            [
                '==========',
                'Test Finished',
                '=========='
            ],
            $result
        ));
        
        return 0;
    }
    
}
