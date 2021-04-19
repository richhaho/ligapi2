<?php

declare(strict_types=1);


namespace App\Services\Pdf\DefaultPdfSpecifications;


use App\Entity\Data\PdfFieldBorder;
use App\Entity\Data\PdfFieldDimensions;
use App\Entity\Data\PdfSpecificationType;
use App\Services\Pdf\PdfSpecification;

// Einzeln 90x29mm V2
class PdfLabels6 implements PdfSpecificationInterface
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
        $orderNumberField = new PdfFieldDimensions(
            "lS1Field7",
            44,
            8,
            15,
            0,
            $allBorders
        );
        $categoryField = new PdfFieldDimensions(
            "lS1Field3",
            31,
            8,
            59,
            0,
            $allBorders
        );
        $manufacturerNumberField = new PdfFieldDimensions(
            "lS1Field5",
            44,
            7,
            15,
            8,
            $allBorders
        );
        $mainLocationField = new PdfFieldDimensions(
            "lS1Field2",
            31,
            7,
            59,
            8,
            $allBorders
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
            "l_6",
            'Einzeln 90x29mm V2',
            90,
            29,
            false,
            false,
            0,
            0,
            1,
            1,
            1,
            [$qrField, $orderNumberField, $categoryField, $manufacturerNumberField, $mainLocationField, $nameField],
            [],
            [],
            [],
            [],
            ['qr', 'text', 'text', 'text', 'text', 'text'],
            ['qrCode', 'prio1OrderSourceOrderNumber', 'category', 'manufacturerNumber', 'mainLocationName', 'name'],
            ['qr', 'text', 'text', 'text', 'text', 'text'],
            ['qrCode', 'barcode', 'itemGroup', 'manufacturer', 'home', 'name'],
            ['qr', 'text', 'text', 'text', 'text', 'text'],
            ['qrCode', 'home', null, 'address', null, 'name'],
            6,
            0,
            PdfSpecificationType::label(),
            [90, 29],
            'L',
            null,
            true
        );
    }
}
