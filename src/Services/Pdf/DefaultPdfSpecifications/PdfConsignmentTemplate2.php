<?php

declare(strict_types=1);


namespace App\Services\Pdf\DefaultPdfSpecifications;


use App\Entity\Data\PdfAlignment;
use App\Entity\Data\PdfFieldBorder;
use App\Entity\Data\PdfFieldDimensions;
use App\Entity\Data\PdfSpecificationType;
use App\Services\Pdf\PdfSpecification;


class PdfConsignmentTemplate2 implements PdfSpecificationInterface
{
    public function getPdfSpecification(): PdfSpecification
    {
        $leftMargin = 6;
    
        // Common Fields
        
        $companyLogoField = new PdfFieldDimensions(
            "companyLogo",
            23,
            4.5,
            $leftMargin,
            8
        );
    
        $companyAddressArrayField = new PdfFieldDimensions(
            "companyAddressArray",
            80,
            null,
            $leftMargin,
            14.2,
            null,
            8,
            null
        );
    
        $deliveryAddressArrayField = new PdfFieldDimensions(
            "deliveryAddressArray",
            80,
            34,
            $leftMargin,
            27.4,
            null,
            14,
            null,
            'B'
        );
    
        $deliveryDateField = new PdfFieldDimensions(
            "deliveryDate",
            80,
            null,
            $leftMargin,
            61.5,
            null,
            14,
            null,
            'B'
        );
        
        $titleField = new PdfFieldDimensions(
            "title",
            null,
            null,
            20,
            9.22,
            null,
            16,
            PdfAlignment::center()->getValue(),
            'B'
        );
    
        $consignmentNumberField = new PdfFieldDimensions(
            "consignmentNumberField",
            80,
            null,
            -12.4,
            28.2,
            null,
            14,
            PdfAlignment::right()->getValue()
        );
    
        $consignmentDateTimeField = new PdfFieldDimensions(
            "consignmentDateTime",
            80,
            null,
            -12.4,
            null,
            null,
            14,
            PdfAlignment::right()->getValue()
        );
        
        // Bezeichnung
        $contentTableNameHeaderField = new PdfFieldDimensions(
            "contentTableNameHeader",
            136.8,
            null,
            $leftMargin,
            75,
            PdfFieldBorder::all()->getValue(),
            14,
            null,
            'B',
            0
        );
    
        $contentTableBarcodeHeaderField = new PdfFieldDimensions(
            "contentTableBarcodeHeader",
            38.1,
            null,
            null,
            null,
            PdfFieldBorder::all()->getValue(),
            14,
            null,
            'B',
            0
        );
    
        $contentTableAmountHeaderField = new PdfFieldDimensions(
            "contentTableAmountHeader",
            18,
            null,
            null,
            null,
            PdfFieldBorder::all()->getValue(),
            14,
            null,
            'B',
            null,
            true
        );
    
        // Content Fields
    
        $contentTableNameContentField = new PdfFieldDimensions(
            "contentTableNameContent",
            136.8,
            null,
            $leftMargin,
            null,
            PdfFieldBorder::all()->getValue(),
            14,
            null,
            null,
            0
        );
    
        $contentTableBarcodeContentField = new PdfFieldDimensions(
            "contentTableBarcodeContent",
            38.1,
            null,
            null,
            null,
            PdfFieldBorder::all()->getValue(),
            14,
            null,
            null,
            0
        );
    
        $contentTableAmountContentField = new PdfFieldDimensions(
            "contentTableAmountContent",
            18,
            null,
            null,
            null,
            PdfFieldBorder::all()->getValue(),
            14
        );
    
        return new PdfSpecification(
            "c_2",
            'Delivery notice',
            171.5,
            0,
            false,
            false,
            0,
            0,
            1,
            0,
            1,
            [
                $contentTableNameContentField,
                $contentTableBarcodeContentField,
                $contentTableAmountContentField
            ],
            [
                $companyLogoField,
                $companyAddressArrayField,
                $deliveryAddressArrayField,
                $deliveryDateField,
                $titleField,
                $consignmentNumberField,
                $consignmentDateTimeField,
                $contentTableNameHeaderField,
                $contentTableBarcodeHeaderField,
                $contentTableAmountHeaderField
            ],
            [
                'image',
                'text',
                'text',
                'text',
                'text',
                'text',
                'text',
                'text',
                'text',
                'text'
            ],
            [
                '',
                '',
                '',
                '',
                'LIEFERSCHEIN',
                '',
                '',
                'Bezeichnung',
                'Barcode',
                'Menge'
            ],
            [
                'companyLogo',
                'companyDataBlock',
                'deliveryAddressArray',
                'deliveryDate',
                'fixedText',
                'consignmentNumber',
                'consignmentDateTime',
                'fixedText',
                'fixedText',
                'fixedText'
            ],
            [
                'text',
                'text',
                'text'
            ],
            [
                'name',
                'barcode',
                'amount'
            ],
            [],
            [],
            [],
            [],
            3,
            10,
            PdfSpecificationType::consignment()
        );
    }
}
