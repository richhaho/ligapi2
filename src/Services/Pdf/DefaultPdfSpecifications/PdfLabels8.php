<?php

declare(strict_types=1);


namespace App\Services\Pdf\DefaultPdfSpecifications;


use App\Entity\Data\PdfAlignment;
use App\Entity\Data\PdfFieldBorder;
use App\Entity\Data\PdfFieldDimensions;
use App\Entity\Data\PdfSpecificationType;
use App\Services\Pdf\PdfSpecification;

// DinA4 97x42.3mm Universal-Etiketten
class PdfLabels8 implements PdfSpecificationInterface
{
    public function getPdfSpecification(): PdfSpecification
    {
        $allBorders = PdfFieldBorder::all()->getValue();
    
        $qrField = new PdfFieldDimensions(
            "lS1Field1",
            15.7,
            15.4,
            0,
            0,
            $allBorders
        );
        $mainLocationField = new PdfFieldDimensions(
            "lS1Field2",
            38.1,
            9.3,
            15.7,
            0,
            $allBorders
        );
        $categoryField = new PdfFieldDimensions(
            "lS1Field3",
            19.3,
            9.3,
            53.8,
            0,
            $allBorders
        );
        $companyLogoField = new PdfFieldDimensions(
            "lS1Field4",
            23.9,
            9.3,
            73.1,
            0,
            $allBorders,
            null,
            PdfAlignment::center()->getValue()
        );
        $manufacturerNumberField = new PdfFieldDimensions(
            "lS1Field5",
            57.4,
            6.1,
            15.7,
            9.3,
            $allBorders
        );
        $profileImageField = new PdfFieldDimensions(
            "lS1Field6",
            23.9,
            33,
            73.1,
            9.3,
            $allBorders,
            null,
            PdfAlignment::center()->getValue()
        );
        $orderNumberField = new PdfFieldDimensions(
            "lS1Field7",
            73.1,
            7.8,
            0,
            15.4,
            $allBorders
        );
        $nameField = new PdfFieldDimensions(
            "lS1Field8",
            73.1,
            19.1,
            0,
            23.2,
            $allBorders
        );
    
        return new PdfSpecification(
            "l_8",
            'A4 97x42.3mm Universal-Etiketten',
            97,
            42.3,
            false,
            false,
            8.48,
            21.5,
            2,
            6,
            1,
            [$qrField, $mainLocationField, $categoryField, $companyLogoField, $manufacturerNumberField, $profileImageField, $orderNumberField, $nameField],
            [],
            [],
            [],
            [],
            ['qr', 'text', 'text', 'image', 'text', 'image', 'text', 'text'],
            ['qrCode', 'mainLocationName', 'category', 'companyLogo', 'manufacturerNumber', 'profileImage', 'prio1OrderSourceOrderNumber', 'name'],
            ['qr', 'text', 'text', 'image', 'text', 'image', 'text', 'text'],
            ['qrCode', 'home', 'itemGroup', 'companyLogo', 'manufacturer', 'profileImage', 'barcode', 'name'],
            ['qr', 'text', 'text', 'image', 'text', 'image', 'text', 'text'],
            ['qrCode', 'home', null, 'companyLogo', 'address', 'profileImage', null, 'name'],
            8,
            0,
            PdfSpecificationType::label(),
            null,
            null,
            null,
            true
        );
    }
}
