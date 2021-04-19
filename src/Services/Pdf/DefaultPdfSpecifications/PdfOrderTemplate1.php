<?php

declare(strict_types=1);


namespace App\Services\Pdf\DefaultPdfSpecifications;


use App\Entity\Data\PdfAlignment;
use App\Entity\Data\PdfFieldBorder;
use App\Entity\Data\PdfFieldDimensions;
use App\Entity\Data\PdfSpecificationType;
use App\Services\Pdf\PdfSpecification;


class PdfOrderTemplate1 implements PdfSpecificationInterface
{
    public function getPdfSpecification(): PdfSpecification
    {
        $rightAlignment = PdfAlignment::right()->getValue();
        $horizontalMargins = 22;
    
        // Common Fields
        
        $companyLogoField = new PdfFieldDimensions(
            "companyLogoField",
            25,
            25,
            $horizontalMargins * -1,
            $horizontalMargins,
            null,
            null,
            $rightAlignment
        );
    
        $companyLineField = new PdfFieldDimensions(
            "companyLineField",
            80,
            null,
            $horizontalMargins,
            51,
            null,
            8,
            null,
            'U'
        );
    
        $supplierAddressField = new PdfFieldDimensions(
            "supplierAddressField",
            100,
            null,
            $horizontalMargins
        );
    
        $consignmentNumberLabelField = new PdfFieldDimensions(
            "consignmentNumberLabelField",
            37,
            null,
            116,
            72,
            null,
            null,
            null,
            'B',
            0
        );
        
        $consignmentNumberValueField = new PdfFieldDimensions(
            "consignmentNumberValueField",
            37,
            null,
            153,
            null,
            null,
            null,
            null,
            null
        );
    
        $userLabelField = new PdfFieldDimensions(
            "userLabelField",
            37,
            null,
            116,
            null,
            null,
            null,
            null,
            'B',
            0
        );
    
        $userValueField = new PdfFieldDimensions(
            "userValueField",
            37,
            null,
            153,
            null,
            null,
            null,
            null,
            null
        );
    
        $orderDateLabelField = new PdfFieldDimensions(
            "orderDateLabelField",
            37,
            null,
            116,
            null,
            null,
            null,
            null,
            'B',
            0
        );
    
        $orderDateValueField = new PdfFieldDimensions(
            "orderDateValueField",
            37,
            null,
            153
        );
    
        $orderTitleField = new PdfFieldDimensions(
            "orderTitleField",
            null,
            null,
            $horizontalMargins,
            98,
            null,
            16,
            null,
            'B',
            16
        );
        
        $salutationField = new PdfFieldDimensions("salutationField");
        
        $introductionField = new PdfFieldDimensions(
            "introductionField", null, null, null, null, null, null, null, null,
            10
        );
        
        $positionHeadingField = new PdfFieldDimensions(
            "positionHeadingField",
            16,
            null,
            null,
            null,
            PdfFieldBorder::bottom()->getValue(),
            null,
            null,
            'B',
            0
        );
        
        $nameHeadingField = new PdfFieldDimensions(
            "nameHeadingField",
            75.5,
            null,
            null,
            null,
            PdfFieldBorder::bottom()->getValue(),
            null,
            null,
            'B',
            0
        );
        
        $orderNumberHeadingField = new PdfFieldDimensions(
            "orderNumberHeadingField",
            40,
            null,
            null,
            null,
            PdfFieldBorder::bottom()->getValue(),
            null,
            null,
            'B',
            0
        );
        
        $amountHeadingField = new PdfFieldDimensions(
            "amountHeadingField",
            20,
            null,
            null,
            null,
            PdfFieldBorder::bottom()->getValue(),
            null,
            null,
            'B',
            0
        );
        
        $unitHeadingField = new PdfFieldDimensions(
            "unitHeadingField",
            20,
            null,
            null,
            null,
            PdfFieldBorder::bottom()->getValue(),
            null,
            null,
            'B',
            null,
            true
        );
    
        $greetingsField = new PdfFieldDimensions("greetingsField", null, null, $horizontalMargins);
    
        $userNameField = new PdfFieldDimensions("userNameField", null, null, null, null, null, null, null, null, 10);
    
        $phoneField = new PdfFieldDimensions("phoneField", null, null, null, null, null, null, null, null, null, null, true);
    
        $faxField = new PdfFieldDimensions("faxField", null, null, null, null, null, null, null, null, null, null, true);
    
        $emailField = new PdfFieldDimensions("emailField", null, null, null, null, null, null, null, null, null, null, true);
    
        $websiteField = new PdfFieldDimensions("websiteField", null, null, null, null, null, null, null, null, null, null, true);
    
    
        // Content Fields
    
        $positionContentField = new PdfFieldDimensions(
            "positionContentField",
            16,
            null,
            $horizontalMargins,
            null,
            null,
            null,
            null,
            null,
            0
        );
    
        $nameContentField = new PdfFieldDimensions(
            "nameContentField",
            75.5,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            0
        );
    
        $orderNumberContentField = new PdfFieldDimensions(
            "orderNumberContentField",
            40,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            0
        );
    
        $amountContentField = new PdfFieldDimensions(
            "amountContentField",
            20,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            0
        );
    
        $unitContentField = new PdfFieldDimensions(
            "unitContentField",
            20,
            null,
            null,
            null,
            null,
            null,
            null,
            null
        );
    
        return new PdfSpecification(
            "o_1",
            'Order',
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
                $positionContentField,
                $nameContentField,
                $orderNumberContentField,
                $amountContentField,
                $unitContentField
            ],
            [
                $companyLogoField,
                $companyLineField,
                $supplierAddressField,
                $consignmentNumberLabelField,
                $consignmentNumberValueField,
                $userLabelField,
                $userValueField,
                $orderDateLabelField,
                $orderDateValueField,
                $orderTitleField,
                $salutationField,
                $introductionField,
                $positionHeadingField,
                $nameHeadingField,
                $orderNumberHeadingField,
                $amountHeadingField,
                $unitHeadingField,
                $greetingsField,
                $userNameField,
                $phoneField,
                $faxField,
                $emailField,
                $websiteField
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
                'Bestellung:',
                '',
                'Sachbearbeiter:',
                '',
                'Datum:',
                '',
                '',
                'Sehr geehrte Damen und Herren,',
                'wir bestellen zum nächstmöglichen Zeitpunkt folgende Artikel:',
                'Pos.',
                'Bezeichnung',
                'Bestellnummer',
                'Menge',
                'Einheit',
                'Mit freundlichen Grüßen,',
                '',
                '',
                '',
                '',
                ''
            ],
            [
                'companyLogo',
                'companyLine',
                'supplierAddress',
                'fixedText',
                'consignmentNumber',
                'fixedText',
                'userFullName',
                'fixedText',
                'orderDate',
                'materialOrderTitle',
                'fixedText',
                'fixedText',
                'fixedText',
                'fixedText',
                'fixedText',
                'fixedText',
                'fixedText',
                'fixedText',
                'userFullName',
                'companyPhone',
                'companyFax',
                'userEmail',
                'companyWebsite'
            ],
            [
                'text',
                'text',
                'text',
                'text',
                'text'
            ],
            [
                'position',
                'name',
                'orderNumber',
                'amount',
                'unitWithAmountPerUnit'
            ],
            [],
            [],
            [],
            [],
            5,
            23,
            PdfSpecificationType::order()
        );
    }
}
