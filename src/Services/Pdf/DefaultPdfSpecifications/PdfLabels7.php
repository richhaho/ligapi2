<?php

declare(strict_types=1);


namespace App\Services\Pdf\DefaultPdfSpecifications;


use App\Entity\Data\PdfAlignment;
use App\Entity\Data\PdfFieldBorder;
use App\Entity\Data\PdfFieldDimensions;
use App\Entity\Data\PdfSpecificationType;
use App\Services\Pdf\PdfSpecification;

// Einzeln 90x29mm V3
class PdfLabels7 implements PdfSpecificationInterface
{
    public function getPdfSpecification(): PdfSpecification
    {
        $allBorders = PdfFieldBorder::all()->getValue();
    
        $qrField = new PdfFieldDimensions(
            "lS1Field1",
            15,
            15,
            0,
            0,
            $allBorders
        );
        $mainLocationField = new PdfFieldDimensions(
            "lS1Field2",
            52,
            8,
            15,
            0,
            $allBorders
        );
        $manufacturerNumberField = new PdfFieldDimensions(
            "lS1Field5",
            52,
            7,
            15,
            8,
            $allBorders
        );
        $companyLogoField = new PdfFieldDimensions(
            "lS1Field4",
            23,
            15,
            67,
            0,
            $allBorders,
            null,
            PdfAlignment::center()->getValue()
        );
        $nameField = new PdfFieldDimensions(
            "lS1Field8",
            90,
            14,
            0,
            15,
            $allBorders
        );
    
        return new PdfSpecification(
            "l_7",
            'Einzeln 90x29mm V3',
            90,
            29,
            false,
            false,
            0,
            0,
            1,
            1,
            1,
            [$qrField, $mainLocationField, $manufacturerNumberField, $companyLogoField, $nameField],
            [],
            [],
            [],
            [],
            ['qr', 'text', 'text', 'image', 'text'],
            ['qrCode', 'mainLocationName', 'manufacturerNumber', 'companyLogo', 'name'],
            ['qr', 'text', 'text', 'image', 'text'],
            ['qrCode', 'home', 'barcode', 'companyLogo', 'name'],
            ['qr', 'text', 'text', 'image', 'text'],
            ['qrCode', 'home', 'address', 'companyLogo', 'name'],
            5,
            0,
            PdfSpecificationType::label(),
            [90, 29],
            'L',
            null,
            true
        );
    }
}
