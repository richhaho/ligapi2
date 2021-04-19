<?php

declare(strict_types=1);


namespace App\Services\Pdf\DefaultPdfSpecifications;


use App\Entity\Data\PdfAlignment;
use App\Entity\Data\PdfFieldBorder;
use App\Entity\Data\PdfFieldDimensions;
use App\Entity\Data\PdfSpecificationType;
use App\Services\Pdf\PdfSpecification;

// A4 43x74 mm
class PdfLabels10 implements PdfSpecificationInterface
{
    public function getPdfSpecification(): PdfSpecification
    {
        $allBorders = PdfFieldBorder::all()->getValue();
    
        $profileImageField = new PdfFieldDimensions(
            "lS1Field6",
            43,
            27.5,
            0,
            0,
            $allBorders,
            null,
            PdfAlignment::center()->getValue()
        );
        $nameField = new PdfFieldDimensions(
            "lS1Field8",
            43,
            16,
            0,
            27.5,
            $allBorders
        );
        $iconField = new PdfFieldDimensions(
            "lS1Field5",
            9,
            9,
            0,
            43.5,
            $allBorders,
            null,
            PdfAlignment::center()->getValue()
        );
        $mainLocationField = new PdfFieldDimensions(
            "lS1Field2",
            25,
            9,
            9,
            43.5,
            $allBorders
        );
        $categoryField = new PdfFieldDimensions(
            "lS1Field3",
            9,
            9,
            34,
            43.5,
            $allBorders
        );
        $qrField = new PdfFieldDimensions(
            "lS1Field1",
            21.5,
            21.5,
            0,
            52.5,
            $allBorders
        );
        $companyLogoField = new PdfFieldDimensions(
            "lS1Field4",
            21.5,
            21.5,
            21.5,
            52.5,
            $allBorders,
            null,
            PdfAlignment::center()->getValue()
        );
    
        return new PdfSpecification(
            "l_10",
            'A4 43x74 mm',
            43,
            74,
            true,
            true,
            7,
            11,
            4,
            3,
            1,
            [$profileImageField, $nameField, $iconField, $mainLocationField, $categoryField, $qrField, $companyLogoField],
            [],
            [],
            [],
            [],
            ['image', 'text', 'image', 'text', 'text', 'qr', 'image'],
            ['profileImage', 'name', 'icon', 'mainLocationName', 'category', 'qrCode', 'companyLogo'],
            ['image', 'text', 'image', 'text', 'text', 'qr', 'image'],
            ['profileImage', 'name', 'icon', 'home', 'itemCategory', 'qrCode', 'companyLogo'],
            ['image', 'text', 'image', 'text', 'text', 'qr', 'image'],
            ['profileImage', 'name', 'icon', 'home', 'address', 'qrCode', 'companyLogo'],
            7,
            0,
            PdfSpecificationType::label(),
            null,
            null,
            null,
            true
        );
    }
}
