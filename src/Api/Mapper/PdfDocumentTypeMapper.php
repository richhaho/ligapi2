<?php

declare(strict_types=1);


namespace App\Api\Mapper;


use App\Api\Dto\PdfDocumentTypeDto;
use App\Entity\Data\ChangeAction;
use App\Entity\Data\EntityType;
use App\Entity\Data\PdfField;
use App\Entity\PdfDocumentType;
use App\Event\ChangeEvent;
use App\Exceptions\Domain\MissingDataException;
use App\Repository\PdfDocumentTypeRepository;
use App\Services\CurrentUserProvider;
use App\Services\Pdf\DefaultPdfSpecifications\PdfSpecificationInterface;
use App\Services\Pdf\PdfSpecification;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class PdfDocumentTypeMapper
{
    use ValidationTrait;
    
    
    private EventDispatcherInterface $eventDispatcher;
    private ValidatorInterface $validator;
    private CurrentUserProvider $currentUserProvider;
    private PdfDocumentTypeRepository $pdfDocumentTypeRepository;
    private iterable $pdfSpecifications;
    
    public function __construct(
        CurrentUserProvider $currentUserProvider,
        EventDispatcherInterface $eventDispatcher,
        ValidatorInterface $validator,
        PdfDocumentTypeRepository $pdfDocumentTypeRepository,
        iterable $pdfSpecifications
    )
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->validator = $validator;
        $this->currentUserProvider = $currentUserProvider;
        $this->pdfDocumentTypeRepository = $pdfDocumentTypeRepository;
        $this->pdfSpecifications = $pdfSpecifications;
    }
    
    public function createLabelTypeFromDto(PdfDocumentTypeDto $pdfDocumentTypeDto): PdfDocumentType
    {
        $this->validate($pdfDocumentTypeDto);
    
        $labelSpecification = null;
        /** @var PdfSpecificationInterface $pdfSpecification */
        foreach ($this->pdfSpecifications as $pdfSpecification) {
            if ($pdfSpecification->getPdfSpecification()->getId() === $pdfDocumentTypeDto->pdfSpecification->id) {
                $labelSpecification = $pdfSpecification->getPdfSpecification();
            }
        }
        
        if (!$labelSpecification) {
            throw MissingDataException::forEntityNotFound($pdfDocumentTypeDto->pdfSpecification->id, PdfSpecification::class);
        }
        
        $itemPdfFields = [];
        if ($pdfDocumentTypeDto->itemFields) {
            foreach ($pdfDocumentTypeDto->itemFields as $itemPdfField) {
                $itemPdfFields[] = PdfField::fromArray($itemPdfField);
            }
        }
        
        $commonPdfFields = [];
        if ($pdfDocumentTypeDto->commonFields) {
            foreach ($pdfDocumentTypeDto->commonFields as $commonPdfField) {
                $commonPdfFields[] = PdfField::fromArray($commonPdfField);
            }
        }
        
        $labelType = new PdfDocumentType(
            $this->currentUserProvider->getCompany(),
            $pdfDocumentTypeDto->name,
            EntityType::fromString($pdfDocumentTypeDto->entityType),
            $labelSpecification,
            $itemPdfFields,
            $commonPdfFields
        );
        
        $labelType->setPdfSpecification($labelSpecification);
    
        $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::create(), $labelType));
        
        return $labelType;
    }
    
    public function putLabelTypeFromDto(PdfDocumentTypeDto $pdfDocumentTypeDto, PdfDocumentType $labelType): PdfDocumentType
    {
        $pdfDocumentTypeDto->id = $labelType->getId();
        
        $this->validate($pdfDocumentTypeDto);
        
        $labelType->setName($pdfDocumentTypeDto->name);
        
        $labelType->removeAllItemFields();
    
        foreach ($pdfDocumentTypeDto->itemFields as $itemPdfField) {
            $labelType->addItemField(PdfField::fromArray($itemPdfField));
        }
        
        $labelType->removeAllCommonFields();
    
        foreach ($pdfDocumentTypeDto->commonFields as $commonField) {
            $labelType->addCommonField(PdfField::fromArray($commonField));
        }
    
        $this->eventDispatcher->dispatch(new ChangeEvent(ChangeAction::update(), $labelType));
        
        return $labelType;
    }
}
