<?php

declare(strict_types=1);


namespace App\Commands;


use App\Entity\Data\AutoStatus;
use App\Entity\Data\MaterialOrderStatus;
use App\Entity\DirectOrderPositionResult;
use App\Entity\Material;
use App\Entity\OrderSource;
use App\Repository\MaterialOrderRepository;
use App\Services\Crawler\Messages\GetMaterialData;
use App\Services\Crawler\Messages\OrderMaterials;
use App\Services\Crawler\Messages\ProcessDirectOrderPositionResults;
use App\Services\Crawler\Messages\UpdatePrices;
use App\Services\DirectOrderService;
use App\Services\MaterialService;
use App\Services\OrderSourceService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class MessengerCommand extends Command
{
    private MessageBusInterface $messageBus;
    private OrderSourceService $orderSourceService;
    private MaterialService $materialService;
    private MaterialOrderRepository $materialOrderRepository;
    private DirectOrderService $directOrderService;
    private EntityManagerInterface $entityManager;
    
    public function __construct(
        MessageBusInterface $messageBus,
        OrderSourceService $orderSourceService,
        MaterialService $materialService,
        MaterialOrderRepository $materialOrderRepository,
        DirectOrderService $directOrderService,
        EntityManagerInterface $entityManager
    )
    {
        parent::__construct();
        
        $this->messageBus = $messageBus;
        $this->orderSourceService = $orderSourceService;
        $this->materialService = $materialService;
        $this->materialOrderRepository = $materialOrderRepository;
        $this->directOrderService = $directOrderService;
        $this->entityManager = $entityManager;
    }
    
    protected function configure()
    {
        $this->setName('app:generate');
    }
    
    private function addOrderSourcesToMessageBus(): void
    {
        /** @var OrderSource[] $orderSourceIdsWithPriceUpdate */
        $orderSourceIdsWithPriceUpdate = $this->orderSourceService->getNextBatchOfOrderSourceWithPriceUpdate(10);
    
        if (!$orderSourceIdsWithPriceUpdate) {
            return;
        }
    
        $updateMaterialPriceDto = new UpdatePrices($orderSourceIdsWithPriceUpdate);
    
        $this->orderSourceService->setStatusOfOrderSources($orderSourceIdsWithPriceUpdate, AutoStatus::running());
        
        $this->messageBus->dispatch($updateMaterialPriceDto);
    }
    
    private function addMaterialsToMessageBus(): void
    {
        /** @var Material[] $materialIdsWithCrawlerSearchTerm */
        $materialIdsWithCrawlerSearchTerm = $this->materialService->getNextBatchOfMaterialIdsWithCrawlerSearchTerm(10);
        
        if (!$materialIdsWithCrawlerSearchTerm) {
            return;
        }
        
        $getMaterialDataDto = new GetMaterialData($materialIdsWithCrawlerSearchTerm);

        $this->materialService->setStatusOfMaterials($materialIdsWithCrawlerSearchTerm, AutoStatus::running());
        
        $this->messageBus->dispatch($getMaterialDataDto);
    }
    
    private function addMaterialOrderMessageBus(): void
    {
        $materialOrder = $this->materialOrderRepository->getNextMaterialOrder();
        
        if (!$materialOrder) {
            return;
        }
        
        $orderMaterial = new OrderMaterials($materialOrder->getId());
    
        $materialOrder->setMaterialOrderStatus(MaterialOrderStatus::processing());
        
        $this->messageBus->dispatch($orderMaterial);
    }
    
    private function addDirectOrderPositionResultMessageBus(): void
    {
        /** @var DirectOrderPositionResult[] $directOrderPositionResults */
        $directOrderPositionResults = $this->directOrderService->getNextBatchOfNewDirectOrderPositionResults(10);
    
        if (!$directOrderPositionResults) {
            return;
        }
    
        $directOrderPositionResultsDto = new ProcessDirectOrderPositionResults($directOrderPositionResults);
    
        $this->directOrderService->setStatusOfDirectOrderPositionResults($directOrderPositionResults, AutoStatus::running());
    
        $this->messageBus->dispatch($directOrderPositionResultsDto);
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $companyFilterWasEnabled = false;
        if ($this->entityManager->getFilters()->isEnabled('company')) {
            $companyFilterWasEnabled = true;
            $this->entityManager->getFilters()->disable('company');
        }
        
        $this->addOrderSourcesToMessageBus();
        $this->addMaterialsToMessageBus();
        $this->addMaterialOrderMessageBus();
        $this->addDirectOrderPositionResultMessageBus();
    
        if ($companyFilterWasEnabled) {
            $this->entityManager->getFilters()->enable('company');
        }
        
        return 0;
    }
    
}
