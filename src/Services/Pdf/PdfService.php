<?php

declare(strict_types=1);


namespace App\Services\Pdf;


use App\Api\Dto\PdfDocumentDto;
use App\Entity\ConsignmentItem;
use App\Entity\Data\PdfField;
use App\Entity\Keyy;
use App\Entity\MaterialOrderPosition;
use App\Entity\PdfDocumentType;
use App\Entity\Material;
use App\Entity\Tool;
use App\Exceptions\Domain\MissingDataException;
use App\Repository\PdfDocumentTypeRepository;
use App\Services\CurrentUserProvider;
use App\Services\Pdf\DefaultPdfSpecifications\PdfSpecificationInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Throwable;

class PdfService
{
    private ManagerRegistry $managerRegistry;
    private array $pdfItemFormatMappings;
    private iterable $itemFieldTransformers;
    private PropertyAccessorInterface $propertyAccessor;
    private PdfPrinterService $pdfPrinterService;
    private int $documentCount;
    private PdfDocumentTypeRepository $pdfDocumentTypeRepository;
    private CurrentUserProvider $currentUserProvider;
    private string $publicPath;
    private iterable $pdfSpecifications;
    
    public function __construct(
        ManagerRegistry $managerRegistry,
        PropertyAccessorInterface $propertyAccessor,
        PdfPrinterService $labelPrinterService,
        PdfDocumentTypeRepository $pdfDocumentTypeRepository,
        CurrentUserProvider $currentUserProvider,
        array $pdfItemFormatMappings,
        iterable $itemFieldTransformers,
        string $publicPath,
        iterable $pdfSpecifications
    )
    {
        $this->documentCount = 1;
        $this->managerRegistry = $managerRegistry;
        $this->pdfItemFormatMappings = $pdfItemFormatMappings;
        $this->itemFieldTransformers = $itemFieldTransformers;
        $this->propertyAccessor = $propertyAccessor;
        $this->pdfPrinterService = $labelPrinterService;
        $this->pdfDocumentTypeRepository = $pdfDocumentTypeRepository;
        $this->currentUserProvider = $currentUserProvider;
        $this->publicPath = $publicPath;
        $this->pdfSpecifications = $pdfSpecifications;
    }
    
    private function getValueFromEntity(object $entity, string $property): string
    {
        if ($property === "null") {
            return '';
        }
        
        if (method_exists($entity, 'getMaterial') && $entity->getMaterial()) {
            $entity = $entity->getMaterial();
        } else if (method_exists($entity, 'getTool') && $entity->getTool()) {
            $entity = $entity->getTool();
        } else if (method_exists($entity, 'getKeyy') && $entity->getKeyy()) {
            $entity = $entity->getKeyy();
        }
    
        try {
            return $this->propertyAccessor->getValue($entity, $property) ?? '';
        } catch (Throwable $e) {
            return '';
        }
    }
    
    /**
     * @param PdfField[] $itemFields
     */
    private function getPdfData(object $entity, array $itemFields, int $position): array
    {
        $data = [];
        foreach ($itemFields as $itemField) {
            foreach ($this->pdfItemFormatMappings as $itemFormatMapping) {
                if ($itemFormatMapping['property'] === $itemField->getProperty()) {
                    if ($itemFormatMapping['transformer']) {
                        foreach ($this->itemFieldTransformers as $transformer) {
                            if ($transformer->supports($itemFormatMapping['transformer'])) {
                                $value = $transformer->transform($entity, $itemField, $position);
                                $data[$itemField->getPosition()] = $value;
                            }
                        }
                    } else {
                        $data[$itemField->getPosition()] = $this->getValueFromEntity(
                            $entity,
                            $itemField->getProperty()
                        );
                    }
                }
            }
        }
        return $data;
    }
    
    public function createDocumentFromPdfDocumentDtoAndEntityType(PdfDocumentDto $pdfDocumentDto): string
    {
        $documents = [];
    
        foreach ($pdfDocumentDto->pdfDocumentTypeIds as $pdfDocumentTypeId) {
            $pdfDocumentType = $this->pdfDocumentTypeRepository->find($pdfDocumentTypeId);
            $newLabelDto = new PdfDocumentDto();
            $newLabelDto->ids = $pdfDocumentDto->ids;
            $newLabelDto->entityType = $pdfDocumentDto->entityType;
            $documents[] = $this->createDocumentFromPdfDocumentDtoAndPdfDocumentType($newLabelDto, $pdfDocumentType);
        }
    
        $companyId = $this->currentUserProvider->getCompany()->getId();
    
        $folder = $this->publicPath . '/companyData/' . $companyId . '/pdf/';
        $fileName = "etiketten.pdf";
    
        $outputName = $folder . $fileName;
    
        $cmd = "gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=$outputName ";
        //Add each pdf file to the end of the command
        foreach($documents as $file) {
            $cmd .= $file." ";
        }
        shell_exec($cmd);
    
        return 'companyData/' . $companyId . '/pdf/' . $fileName;
    }
    
    public function createDocumentFromPdfDocumentDtoAndPdfDocumentType(
        PdfDocumentDto $pdfDocumentDto,
        PdfDocumentType $pdfDocumentType
    ): string
    {
        /** @var PdfSpecificationInterface $pdfSpecification */
        foreach ($this->pdfSpecifications as $pdfSpecification) {
            if ($pdfSpecification->getPdfSpecification()->getId() === $pdfDocumentType->getPdfSpecificationId()) {
                $pdfDocumentType->setPdfSpecification($pdfSpecification->getPdfSpecification());
            }
        }
        
        if (!$pdfDocumentType->getPdfSpecification()) {
            throw MissingDataException::forEntityNotFound($pdfDocumentType->getPdfSpecificationId(), PdfDocumentType::class);
        }
        
        // Get Entities
        $data = [];
        $className = '';
        
        switch ($pdfDocumentDto->entityType) {
            case 'material':
                $className = Material::class;
                break;
            case 'tool':
                $className = Tool::class;
                break;
            case 'keyy':
                $className = Keyy::class;
                break;
            case 'order':
                $className = MaterialOrderPosition::class;
                break;
            case 'consignment':
                $className = ConsignmentItem::class;
                break;
        }
        if (!$className) {
            switch ($pdfDocumentType->getEntityType()) {
                case 'material':
                    $className = Material::class;
                    break;
                case 'tool':
                    $className = Tool::class;
                    break;
                case 'keyy':
                    $className = Keyy::class;
                    break;
                case 'order':
                    $className = MaterialOrderPosition::class;
                    break;
                case 'consignment':
                    $className = ConsignmentItem::class;
                    break;
            }
        }
    
        if (!$className) {
            throw MissingDataException::forMissingData('ClassName');
        }
        
        if (count($pdfDocumentDto->ids) === 0) {
            throw MissingDataException::forMissingData('Items');
        }
        
        /** @var EntityRepository $repository */
        $repository = $this->managerRegistry->getRepository($className);
        foreach ($pdfDocumentDto->ids as $index => $id) {
            $item = $repository->find($id);
            if (!$item) {
                throw MissingDataException::forEntityNotFound($id, $className);
            }
            $data[] = $this->getPdfData(
                $item,
                $pdfDocumentType->getItemFieldsAsItemFields(),
                $index
            );
        }
        
        $commonData = $this->getPdfData(
            $item,
            $pdfDocumentType->getCommonFieldsAsCommonFields(),
            0
        );
        
        // Get Labelspecification
        $labelSpecification = $pdfDocumentType->getPdfSpecification();
        $itemTypes = $labelSpecification->getDefaultFieldTypesMaterial();
        foreach ($pdfDocumentType->getItemFieldsAsItemFields() as $index => $itemField) {
            $itemTypes[$itemField->getPosition()] = $itemField->getType();
        }
        $commonTypes = $labelSpecification->getDefaultCommonFieldTypes();
        foreach ($pdfDocumentType->getCommonFieldsAsCommonFields() as $index => $commonField) {
            $commonTypes[$commonField->getPosition()] = $commonField->getType();
        }
        
        // Create Labels
        $name = $labelSpecification->getPdfSpecificationType()->getFileName() . '_' . $this->documentCount;
        $this->documentCount++;
        return $this->pdfPrinterService->createPdfDocument(
            $name . '.pdf',
            $data,
            $commonData,
            $labelSpecification,
            $itemTypes,
            $commonTypes
        );
    }
}
