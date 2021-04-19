<?php

declare(strict_types=1);


namespace App\Services\Pdf\DefaultPdfSpecifications;


use App\Entity\Data\PdfAlignment;
use App\Entity\Data\PdfFieldDimensions;
use App\Entity\Data\PdfSpecificationType;
use App\Services\Pdf\PdfSpecification;


class PdfConsignmentTemplate4 implements PdfSpecificationInterface
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
    
        return new PdfSpecification(
            "c_4",
            'Repair Sheet',
            171.5,
            0,
            false,
            false,
            0,
            0,
            1,
            0,
            1,
            [],
            [
                $companyLogoField,
                $companyAddressArrayField,
                $deliveryAddressArrayField,
                $deliveryDateField,
                $titleField,
                $consignmentNumberField,
                $consignmentDateTimeField
            ],
            [
                'image',
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
                'Anlieferadresse:',
                '',
                '',
            ],
            [
                'companyLogo',
                'companyDataBlock',
                'deliveryAddressArray',
                'deliveryDate',
                'fixedText',
                'consignmentNumber',
                'consignmentDateTime'
            ],
            [],
            [],
            [],
            [],
            [],
            [],
            0,
            7,
            PdfSpecificationType::consignment(),
            null,
            'P',
            'repairSheet.jpg'
        );
    }
}
