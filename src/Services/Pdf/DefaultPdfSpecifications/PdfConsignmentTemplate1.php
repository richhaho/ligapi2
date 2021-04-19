<?php

declare(strict_types=1);


namespace App\Services\Pdf\DefaultPdfSpecifications;


use App\Entity\Data\PdfAlignment;
use App\Entity\Data\PdfFieldBorder;
use App\Entity\Data\PdfFieldDimensions;
use App\Entity\Data\PdfSpecificationType;
use App\Services\Pdf\PdfSpecification;


class PdfConsignmentTemplate1 implements PdfSpecificationInterface
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
    
        // Auftragsinhalt
        $contentHeaderField = new PdfFieldDimensions(
            "contentHeader",
            80,
            null,
            $leftMargin,
            77.1,
            null,
            14,
            null,
            'B'
        );
    
        $contentSelectionField = new PdfFieldDimensions(
            "contentSelection",
            80,
            null,
            $leftMargin,
            null,
            null,
            14
        );
        
        // Zeit Kommissionierung/Mängel/Doku
        $consignmentDetailsTableHeaderField = new PdfFieldDimensions(
            "consignmentDetailsTableHeader",
            108.2,
            null,
            90.7,
            71,
            PdfFieldBorder::all()->getValue(),
            14,
            PdfAlignment::center()->getValue(),
            'B'
        );
        
        $consignmentDetailsTableDateField = new PdfFieldDimensions(
            "consignmentDetailsTableDate",
            38.1,
            43,
            90.7,
            null,
            PdfFieldBorder::all()->getValue(),
            14,
            null,
            null,
            0
        );
        
        $consignmentDetailsTableFromToField = new PdfFieldDimensions(
            "consignmentDetailsTableFromTo",
            51.8,
            43,
            null,
            null,
            PdfFieldBorder::all()->getValue(),
            14,
            null,
            null,
            0
        );
        
        $consignmentDetailsTableBreakField = new PdfFieldDimensions(
            "consignmentDetailsTableBreak",
            18.3,
            43,
            null,
            null,
            PdfFieldBorder::all()->getValue(),
            14,
            PdfAlignment::center()->getValue(),
            null
        );
    
        // Bezeichnung
        $contentTableNameHeaderField = new PdfFieldDimensions(
            "contentTableNameHeader",
            84.7,
            null,
            $leftMargin,
            125,
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
            0
        );
    
        $contentTableLocationHeaderField = new PdfFieldDimensions(
            "contentTableLocationHeader",
            33.8,
            null,
            null,
            null,
            PdfFieldBorder::all()->getValue(),
            14,
            null,
            'B',
            0
        );
    
        $contentTableLocationStatusField = new PdfFieldDimensions(
            "contentTableLocationStatus",
            18.3,
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
            84.7,
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
            14,
            null,
            null,
            0
        );
    
        $contentTableLocationContentField = new PdfFieldDimensions(
            "contentTableLocationContent",
            33.8,
            null,
            null,
            null,
            PdfFieldBorder::all()->getValue(),
            14,
            null,
            null,
            0
        );
    
        $contentTableStatusContentField = new PdfFieldDimensions(
            "contentTableStatusContent",
            18.3,
            null,
            null,
            null,
            PdfFieldBorder::all()->getValue(),
            14
        );
    
        return new PdfSpecification(
            "c_1",
            'Consignment',
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
                $contentTableAmountContentField,
                $contentTableLocationContentField,
                $contentTableStatusContentField
            ],
            [
                $companyLogoField,
                $companyAddressArrayField,
                $deliveryAddressArrayField,
                $deliveryDateField,
                $titleField,
                $consignmentNumberField,
                $consignmentDateTimeField,
                $contentHeaderField,
                $contentSelectionField,
                $consignmentDetailsTableHeaderField,
                $consignmentDetailsTableDateField,
                $consignmentDetailsTableFromToField,
                $consignmentDetailsTableBreakField,
                $contentTableNameHeaderField,
                $contentTableBarcodeHeaderField,
                $contentTableAmountHeaderField,
                $contentTableLocationHeaderField,
                $contentTableLocationStatusField
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
                'KOMMISSIONIERSCHEIN',
                '',
                '',
                'Anlieferadresse:',
                '',
                'Zeit Kommissionierung/Mängel/Doku',
                'Datum:',
                'von - bis:',
                'Pause',
                'Bezeichnung',
                'Barcode',
                'Menge',
                'Lagerplatz',
                'Status'
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
                'consignmentContentSelection',
                'fixedText',
                'fixedText',
                'fixedText',
                'fixedText',
                'fixedText',
                'fixedText',
                'fixedText',
                'fixedText',
                'fixedText'
            ],
            [
                'text',
                'text',
                'text',
                'text',
                'text'
            ],
            [
                'name',
                'barcode',
                'amount',
                'mainLocationName',
                'null'
            ],
            [],
            [],
            [],
            [],
            5,
            18,
            PdfSpecificationType::consignment()
        );
    }
}
