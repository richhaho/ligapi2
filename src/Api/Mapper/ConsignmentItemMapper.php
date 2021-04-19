<?php

declare(strict_types=1);


namespace App\Api\Mapper;


use App\Api\Dto\CreateConsignmentItemDto;
use App\Api\Dto\PutConsignmentItemDto;
use App\Entity\Consignment;
use App\Entity\ConsignmentItem;
use App\Entity\Data\ChangeAction;
use App\Entity\Data\ConsignmentItemStatus;
use App\Entity\Keyy;
use App\Entity\Material;
use App\Entity\Tool;
use App\Event\ChangeEvent;
use App\Exceptions\Domain\MissingDataException;
use App\Repository\ConsignmentRepository;
use App\Repository\KeyyRepository;
use App\Repository\MaterialRepository;
use App\Repository\ToolRepository;
use App\Services\CurrentUserProvider;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ConsignmentItemMapper
{
    use ValidationTrait;
    
    private CurrentUserProvider $currentUserProvider;
    private ConsignmentRepository $consignmentRepository;
    private MaterialRepository $materialRepository;
    private ToolRepository $toolRepository;
    private KeyyRepository $keyyRepository;
    private EventDispatcherInterface $eventDispatcher;
    private ValidatorInterface $validator;
    
    public function __construct(
        CurrentUserProvider $currentUserProvider,
        ConsignmentRepository $consignmentRepository,
        MaterialRepository $materialRepository,
        ToolRepository $toolRepository,
        KeyyRepository $keyyRepository,
        EventDispatcherInterface $eventDispatcher,
        ValidatorInterface $validator
    )
    {
        $this->currentUserProvider = $currentUserProvider;
        $this->consignmentRepository = $consignmentRepository;
        $this->materialRepository = $materialRepository;
        $this->toolRepository = $toolRepository;
        $this->keyyRepository = $keyyRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->validator = $validator;
    }
    
    public function createConsignmentItemFromDto(CreateConsignmentItemDto $createConsignmentItemDto): ConsignmentItem
    {
        $this->validate($createConsignmentItemDto);
        
        $company = $this->currentUserProvider->getAuthenticatedUser()->getCompany();
        
        $consignment = $this->consignmentRepository->find($createConsignmentItemDto->consignmentId);
        if (!$consignment) {
            throw MissingDataException::forEntityNotFound($createConsignmentItemDto->consignmentId, Consignment::class);
        }
        
        $material = null;
        $tool = null;
        $keyy = null;
        $amount = null;
        
        if ($createConsignmentItemDto->materialId) {
            $material = $this->materialRepository->find($createConsignmentItemDto->materialId);
            if (!$material) {
                throw MissingDataException::forEntityNotFound($createConsignmentItemDto->materialId, Material::class);
            }
            $amount = $createConsignmentItemDto->amount;
        }
        
        if ($createConsignmentItemDto->toolId) {
            $tool = $this->toolRepository->find($createConsignmentItemDto->toolId);
            if (!$tool) {
                throw MissingDataException::forEntityNotFound($createConsignmentItemDto->toolId, Tool::class);
            }
        }
        
        if ($createConsignmentItemDto->keyyId) {
            $keyy = $this->keyyRepository->find($createConsignmentItemDto->keyyId);
            if (!$keyy) {
                throw MissingDataException::forEntityNotFound($createConsignmentItemDto->keyyId, Keyy::class);
            }
        }
        
        $consignmentItem = new ConsignmentItem($company, $consignment, $material, $tool, $keyy, $createConsignmentItemDto->manualName, $amount);
    
        $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::create(), $consignmentItem));
        
        return $consignmentItem;
    }
    
    public function putConsignmentItemFromDto(PutConsignmentItemDto $putConsignmentItemDto, ConsignmentItem $consignmentItem): ConsignmentItem
    {
        $consignmentItem->setAmount($putConsignmentItemDto->amount);
        $consignmentItem->setConsignedAmount($putConsignmentItemDto->consignedAmount);
        $consignmentItem->setConsignmentItemStatus(ConsignmentItemStatus::fromString($putConsignmentItemDto->consignmentItemStatus));
    
        $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::update(), $consignmentItem));
        
        return $consignmentItem;
    }
}
