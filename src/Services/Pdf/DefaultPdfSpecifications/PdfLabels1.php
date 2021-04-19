<?php

declare(strict_types=1);


namespace App\Services\Pdf\DefaultPdfSpecifications;


use App\Entity\Data\PdfAlignment;
use App\Entity\Data\PdfFieldBorder;
use App\Entity\Data\PdfFieldDimensions;
use App\Entity\Data\PdfSpecificationType;
use App\Services\Pdf\PdfSpecification;

// A4 98.4 x 38.1
class PdfLabels1 implements PdfSpecificationInterface
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
            $qrField->getWidth(),
            $qrField->getY(),
            $allBorders
        );
        $categoryField = new PdfFieldDimensions(
            "lS1Field3",
            20.7,
            $mainLocationField->getHeight(),
            $mainLocationField->getX() + $mainLocationField->getWidth(),
            $qrField->getY(),
            $allBorders
        );
        $companyLogoField = new PdfFieldDimensions(
            "lS1Field4",
            23.9,
            $mainLocationField->getHeight(),
            $categoryField->getX() + $categoryField->getWidth(),
            $qrField->getY(),
            $allBorders,
            null,
            PdfAlignment::center()->getValue()
        );
        $manufacturerNumberField = new PdfFieldDimensions(
            "lS1Field5",
            58.8,
            6.1,
            $mainLocationField->getX(),
            $mainLocationField->getHeight(),
            $allBorders
        );
        $profileImageField = new PdfFieldDimensions(
            "lS1Field6",
            $companyLogoField->getWidth(),
            29.8,
            $manufacturerNumberField->getX() + $manufacturerNumberField->getWidth(),
            $mainLocationField->getHeight(),
            $allBorders,
            null,
            PdfAlignment::center()->getValue()
        );
        $orderNumberField = new PdfFieldDimensions(
            "lS1Field7",
            74.5,
            7.8,
            $qrField->getX(),
            $qrField->getHeight(),
            $allBorders
        );
        $nameField = new PdfFieldDimensions(
            "lS1Field8",
            $orderNumberField->getWidth(),
            15.9,
            $qrField->getX(),
            $orderNumberField->getY() + $orderNumberField->getHeight(),
            $allBorders
        );
    
        return new PdfSpecification(
            "l_1",
            'A4 98.4x38.1mm',
            98.4,
            39.1,
            true,
            true,
            7,
            11,
            2,
            7,
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
