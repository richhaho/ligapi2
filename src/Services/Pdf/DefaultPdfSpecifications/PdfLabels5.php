<?php

declare(strict_types=1);


namespace App\Services\Pdf\DefaultPdfSpecifications;


use App\Entity\Data\PdfAlignment;
use App\Entity\Data\PdfFieldBorder;
use App\Entity\Data\PdfFieldDimensions;
use App\Entity\Data\PdfSpecificationType;
use App\Services\Pdf\PdfSpecification;

// Einzeln 90x29xmm V1
class PdfLabels5 implements PdfSpecificationInterface
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
            31,
            8,
            15,
            0,
            $allBorders
        );
        $categoryField = new PdfFieldDimensions(
            "lS1Field3",
            21,
            8,
            46,
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
        $orderNumberField = new PdfFieldDimensions(
            "lS1Field7",
            67,
            7,
            0,
            15,
            $allBorders
        );
        $profileImageField = new PdfFieldDimensions(
            "lS1Field6",
            23,
            22,
            67,
            0,
            $allBorders,
            null,
            PdfAlignment::center()->getValue()
        );
        $nameField = new PdfFieldDimensions(
            "lS1Field8",
            90,
            7,
            0,
            22,
            $allBorders
        );
    
        return new PdfSpecification(
            "l_5",
            'Einzeln 90x29mm V1',
            90,
            29,
            false,
            false,
            0,
            0,
            1,
            1,
            1,
            [$qrField, $mainLocationField, $categoryField, $manufacturerNumberField, $orderNumberField, $profileImageField, $nameField],
            [],
            [],
            [],
            [],
            ['qr', 'text', 'text', 'text', 'text', 'image', 'text'],
            ['qrCode', 'mainLocationName', 'category', 'manufacturerNumber', 'prio1OrderSourceOrderNumber', 'profileImage', 'name'],
            ['qr', 'text', 'text', 'text', 'text', 'image', 'text'],
            ['qrCode', 'home', 'itemGroup', 'manufacturer', 'barcode', 'profileImage', 'name'],
            ['qr', 'text', 'text', 'text', 'image', 'text', 'text'],
            ['qrCode', 'home', null, 'address', null, 'profileImage', 'name'],
            7,
            0,
            PdfSpecificationType::label(),
            [90, 29],
            'L',
            null,
            true
        );
    }
}
