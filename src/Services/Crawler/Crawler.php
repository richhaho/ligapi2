<?php

declare(strict_types=1);

namespace App\Services\Crawler;

use App\Api\Dto\AutoMaterialDto;
use App\Api\Mapper\MaterialMapper;
use App\Entity\Data\AutoStatus;
use App\Entity\Data\MaterialOrderStatus;
use App\Entity\DirectOrderPositionResult;
use App\Entity\Material;
use App\Entity\MaterialOrder;
use App\Entity\OrderSource;
use App\Entity\Supplier;
use App\Exceptions\Domain\InconsistentDataException;
use App\Exceptions\Domain\MissingDataException;
use App\Repository\DirectOrderPositionResultRepository;
use App\Repository\MaterialOrderRepository;
use App\Repository\MaterialRepository;
use App\Repository\OrderSourceRepository;
use App\Security\Secrets\SodiumEncrypter;
use App\Services\Crawler\Dto\AvailabilityInfosDto;
use App\Services\DirectOrderService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Routing\RequestContext;
use Throwable;

class Crawler
{
    private iterable $readers;
    private EntityManagerInterface $entityManager;
    private OrderSourceRepository $orderSourceRepository;
    private SodiumEncrypter $sodiumEncrypter;
    private MaterialRepository $materialRepository;
    private MaterialMapper $materialMapper;
    private MaterialOrderRepository $materialOrderRepository;
    private DirectOrderPositionResultRepository $directOrderPositionResultRepository;
    private DirectOrderService $directOrderService;
    private RequestContext $requestContext;
    
    public function __construct(
        iterable $readers,
        EntityManagerInterface $entityManager,
        OrderSourceRepository $orderSourceRepository,
        MaterialRepository $materialRepository,
        SodiumEncrypter $sodiumEncrypter,
        MaterialMapper $materialMapper,
        RequestContext $requestContext,
        MaterialOrderRepository $materialOrderRepository,
        DirectOrderPositionResultRepository $directOrderPositionResultRepository,
        DirectOrderService $directOrderService
    )
    {
        $this->readers = $readers;
        $this->entityManager = $entityManager;
        $this->orderSourceRepository = $orderSourceRepository;
        $this->sodiumEncrypter = $sodiumEncrypter;
        $this->materialRepository = $materialRepository;
        $this->materialMapper = $materialMapper;
        $this->materialOrderRepository = $materialOrderRepository;
        $this->directOrderPositionResultRepository = $directOrderPositionResultRepository;
        $this->directOrderService = $directOrderService;
        $this->requestContext = $requestContext;
    }
    
    private function findReader(Supplier $supplier): ?ReaderInterface
    {
        foreach ($this->readers as $reader) {
            if ($reader->supports($supplier)) {
                return $reader;
            }
        }
        return null; //TODO: Exception
    }
    
    public function getMaterialDataForOneMaterial(Material $material, Supplier $supplier, ?bool $login = false): AutoMaterialDto
    {
        $reader = $this->findReader($supplier);
        
        if ($login) {
            $reader->login(
                $supplier->getWebShopLogin(),
                $this->sodiumEncrypter->decrypt($supplier->getEncryptedwebShopPassword()),
                $supplier->getCustomerNumber()
            );
        }
        
        try {
            $materialCrawlerDto = $reader->readAllDetails($material->getAutoSearchTerm(), $material->getCompany());
        } catch (Exception $e) {
            $materialCrawlerDto = new AutoMaterialDto();
            $materialCrawlerDto->note = $e->getMessage();
            $materialCrawlerDto->name = $material->getName();
            $material->setAutoStatus(AutoStatus::error());
            throw $e;
        }
    
        $materialCrawlerDto->orderSources[0]->supplier->id = $supplier->getId();
        
        if ($login) {
            $reader->logout();
            $reader->quit();
        }
    
        return $materialCrawlerDto;
    }
    
    /**
     * @param string[] $materialIds
     *
     * @TODO Catch scenario exceptions and throw domain exceptions
     */
    public function setMaterialData(array $materialIds): void
    {
        $this->requestContext->setParameter('changeSource', 'website');
        
        $firstMaterial = $this->materialRepository->find($materialIds[0]);
        if (!$firstMaterial) {
            throw MissingDataException::forEntityNotFound($materialIds[0], Material::class);
        }
        $supplier = $firstMaterial->getAutoSupplier();
        if (!$supplier) {
            throw InconsistentDataException::forAutoSupplierMissing($firstMaterial->getId());
        }
    
        $reader = $this->findReader($supplier);
    
        $reader->login(
            $supplier->getWebShopLogin(),
            $this->sodiumEncrypter->decrypt($supplier->getEncryptedwebShopPassword()),
            $supplier->getCustomerNumber()
        );
    
        foreach ($materialIds as $index => $materialId) {
            /** @var Material $material */
            $material = $this->materialRepository->find($materialId);
            if (!$material) {
                throw MissingDataException::forEntityNotFound($materialId, Material::class);
            }
            
            $materialDto = $this->getMaterialDataForOneMaterial($material, $supplier);
    
            $this->materialMapper->putMaterialFromAutoMaterialDto($materialDto, $material, $supplier);
    
            $this->entityManager->flush();
        }

        $reader->logout();
        $reader->quit();
    }
    
    public function getAvailabilityInfosForOneOrderSource(OrderSource $orderSource, ?bool $login = false): AvailabilityInfosDto
    {
        $supplier = $orderSource->getSupplier();
        $reader = $this->findReader($supplier);
        
        if ($login) {
            $reader->login(
                $supplier->getWebShopLogin(),
                $this->sodiumEncrypter->decrypt($supplier->getEncryptedwebShopPassword()),
                $supplier->getCustomerNumber()
            );
        }
        
        $availabilityInfos = $reader->readAvailabilityInfosForSearchTerm($orderSource->getOrderNumber());
    
        if ($login) {
            $reader->logout();
            $reader->quit();
        }
        
        return $availabilityInfos;
    }
    
    /**
     * @param string[] $orderSourceIds
     *
     * @TODO Catch scenario exceptions and throw domain exceptions
     */
    public function updatePriceForOrderSource(array $orderSourceIds): void
    {
        $this->requestContext->setParameter('changeSource', 'website');
        
        $firstOrderSource = $this->orderSourceRepository->find($orderSourceIds[0]);
        if (!$firstOrderSource) {
            throw MissingDataException::forEntityNotFound($orderSourceIds[0], OrderSource::class);
        }
    
        $supplier = $firstOrderSource->getSupplier();
        
        $reader = $this->findReader($supplier);
        
        $reader->login($supplier->getWebShopLogin(), $this->sodiumEncrypter->decrypt($supplier->getEncryptedwebShopPassword()), $supplier->getCustomerNumber());
    
        foreach ($orderSourceIds as $index => $orderSourceId) {
            $orderSource = $this->orderSourceRepository->find($orderSourceId);
            if (!$orderSource) {
                throw MissingDataException::forEntityNotFound($orderSourceId, OrderSource::class);
            }
            $availabilityInfosDto = $this->getAvailabilityInfosForOneOrderSource($orderSource);
            
            $orderSource->setPriceAsMoney($availabilityInfosDto->getPrice());
            $orderSource->setAutoStatus(null);
            
            $this->entityManager->flush();
        }
        
        $reader->logout();
        $reader->quit();
    }

    /**
     * @param string $materialOrderId
     *
     * @TODO Catch scenario exceptions and throw domain exceptions
     */
    public function orderMaterials(string $materialOrderId): void
    {
        $this->requestContext->setParameter('changeSource', 'crawler');
        
        /** @var MaterialOrder $materialOrder */
        $materialOrder = $this->materialOrderRepository->find($materialOrderId);
        if (!$materialOrder) {
            throw MissingDataException::forEntityNotFound($materialOrderId, MaterialOrder::class);
        }
    
        $supplier = $materialOrder->getSupplier();
    
        $reader = $this->findReader($supplier);
    
        $reader->login($supplier->getWebShopLogin(), $this->sodiumEncrypter->decrypt($supplier->getEncryptedwebShopPassword()), $supplier->getCustomerNumber());
    
        foreach ($materialOrder->getMaterialOrderPositions() as $index => $materialOrderPosition) {
            $orderSource = $materialOrderPosition->getOrderSource();
            $status = $reader->orderMaterial($orderSource->getOrderNumber(), $materialOrderPosition->getAmount(), $orderSource->getPrice());
            $materialOrderPosition->setStatusMessage($status);
        }
    
        $reader->logout();
        $reader->quit();
    
        $materialOrder->setMaterialOrderStatus(MaterialOrderStatus::complete());
        
        $this->entityManager->flush();
    }
    
    public function processDirectOrderPositionResults(array $directOrderPositionResultIds)
    {
        $this->requestContext->setParameter('changeSource', 'crawler');
        
        $firstDirectOrderPositionResult = $this->directOrderPositionResultRepository->find($directOrderPositionResultIds[0]);
        if (!$firstDirectOrderPositionResult) {
            throw MissingDataException::forEntityNotFound($directOrderPositionResultIds[0], DirectOrderPositionResult::class);
        }
    
        $supplier = $firstDirectOrderPositionResult->getOrderSource()->getSupplier();
    
        $reader = $this->findReader($supplier);
    
        $reader->login($supplier->getWebShopLogin(), $this->sodiumEncrypter->decrypt($supplier->getEncryptedwebShopPassword()), $supplier->getCustomerNumber());
    
        foreach ($directOrderPositionResultIds as $index => $directOrderPositionResultId) {
            $directOrderPositionResult = $this->directOrderPositionResultRepository->find($directOrderPositionResultId);
            if (!$directOrderPositionResult) {
                throw MissingDataException::forEntityNotFound($directOrderPositionResultId, DirectOrderPositionResult::class);
            }
            try {
                $availabilityInfosDto = $this->getAvailabilityInfosForOneOrderSource($directOrderPositionResult->getOrderSource());
            } catch (Throwable $e) {
                $directOrderPositionResult->getDirectOrderPosition()->getDirectOrder()->setStatus(AutoStatus::error());
                $directOrderPositionResult->getDirectOrderPosition()->getDirectOrder()->setStatusDetails($e->getMessage());
                throw $e;
            }
    
            $directOrderPositionResult->getOrderSource()->setPriceAsMoney($availabilityInfosDto->getPrice());
            $directOrderPositionResult->setAvailability($availabilityInfosDto->getAvailability());
            $directOrderPositionResult->setAutoStatus(AutoStatus::complete());
            
            $this->entityManager->flush();
        }
    
        $reader->logout();
        $reader->quit();
        
        if (isset($directOrderPositionResult)) {
            $this->directOrderService->checkDirectOrderStatusAndSendConfirmation(
                $directOrderPositionResult->getDirectOrderPosition()->getDirectOrder()
            );
        }
    }
}
