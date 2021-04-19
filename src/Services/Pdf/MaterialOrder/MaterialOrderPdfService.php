<?php

declare(strict_types=1);


namespace App\Services\Pdf\MaterialOrder;


use App\Api\Dto\PdfDocumentDto;
use App\Entity\Company;
use App\Entity\MaterialOrder;
use App\Entity\MaterialOrderPosition;
use App\Entity\Supplier;
use App\Entity\User;
use App\Repository\PdfDocumentTypeRepository;
use App\Services\Pdf\DefaultPdfSpecifications\PdfOrderTemplate1;
use App\Services\Pdf\PdfService;
use DateTime;

class MaterialOrderPdfService
{
    private PdfDocumentTypeRepository $pdfDocumentTypeRepository;
    private PdfService $pdfService;
    
    public function __construct(
        PdfDocumentTypeRepository $pdfDocumentTypeRepository,
        PdfService $pdfService
    )
    {
        $this->pdfDocumentTypeRepository = $pdfDocumentTypeRepository;
        $this->pdfService = $pdfService;
    }
    
    private function getCompanyLine(): string
    {
        return $this->company->getName() . ' | ' .
            $this->company->getStreet() . ', ' .
            $this->company->getZip() . ' ' . $this->company->getCity() . ' | ' .
            'Tel. ' . $this->company->getPhone() . ' | ' .
            'Fax ' . $this->company->getFax() . ' | ' .
            $this->company->getEmail() . ' | ' .
            $this->company->getUrl()
        ;
    }
    
    private function getOrderPosition(MaterialOrderPosition $materialOrderPosition, $index): array
    {
        return [
            'number' => $index . '.',
            'name' => $materialOrderPosition->getOrderSource()->getMaterial()->getName(),
            'orderNumber' => $materialOrderPosition->getOrderSource()->getOrderNumber(),
            'amount' => $materialOrderPosition->getAmount(),
            'unit' => $materialOrderPosition->getOrderSource()->getMaterial()->getUnit()
        ];
    }
    
    private function getMaterialOrderPositions(): array
    {
        $positions = [];
        $index = 1;
    
        foreach ($this->materialOrder->getMaterialOrderPositions() as $materialOrderPosition) {
            $positions[] = $this->getOrderPosition($materialOrderPosition, $index);
            $index++;
        }
        
        return $positions;
    }
    
    private function setData(MaterialOrder $materialOrder): void
    {
        $this->company = $materialOrder->getCompany();
        $this->materialOrder = $materialOrder;
        $this->supplier = $materialOrder->getSupplier();
        $this->currentUser = $this->currentUserProvider->getAuthenticatedUser();
    }
    
    public function createMaterialOrder(): void
    {
//        $logoPath = $materialOrder->getCompany()->getLogoUrl();
//        if (!$logoPath) {
//            $logoPath = $this->relativeFolder . '/logo.jpg';
//        }
        
//        $logoAlign = 'C';
//        switch($this->company->getLogoPosition()) {
//            case 0:
//                $logoAlign = 'L';
//                break;
//            case 2:
//                $logoAlign = 'R';
//                break;
//
//        }
//
//        $this->setLogo($logoPath, 15, 15, 180, 30, $logoAlign);
        
        $this->setRecepientAddress($this->supplier->getAddressArray());
        
        $this->setTitle(
            'Bestellung vom ' . (new DateTime())->format('d.m.Y'),
            null,
            "Sehr geehrte Damen und Herren, \n\nwir bestellen zum nächstmöglichen Zeitpunkt folgende Artikel: "
        );
        
        $tableHeaderInformation = ['Pos.', 'Name/Beschreibung', 'Bestellnummer', 'Menge', 'Einheit'];
        
        $this->setTableHeader($tableHeaderInformation);
        
        $this->setMaterialOrderPositions($this->getMaterialOrderPositions());
        
        $remark = $this->materialOrder->getDeliveryNote() ? 'Anmerkung: ' . $this->materialOrder->getDeliveryNote() . "\n\n" : '';
        
        $this->setBodyText(sprintf(
            $remark .
            "Mit freundlichen Grüssen,\n%s\n\n%s\n%s",
            $this->currentUser->getFirstName() . ' ' . $this->currentUser->getLastName(),
            $this->company->getEmail(),
            $this->company->getPhone())
        );
        
        $this->setCompanyLine($this->getCompanyLine());
    }
    
    public function createSingleMaterialOrder(MaterialOrder $materialOrder): string
    {
//        $pdfDocumentSpecification = new PdfOrderTemplate1
        $orderDocumentType = $this->pdfDocumentTypeRepository->getOrderPdfDocumentType();
        
        $labelDto = new PdfDocumentDto();
        $ids = [];
        foreach ($materialOrder->getMaterialOrderPositions() as $materialOrderPosition) {
            $ids[] = $materialOrderPosition->getId();
        }
        $labelDto->ids = $ids;
        
        
        return $this->pdfService->createDocumentFromPdfDocumentDtoAndPdfDocumentType($labelDto, $orderDocumentType);
    }
}
