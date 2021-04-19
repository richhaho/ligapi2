<?php

declare(strict_types=1);


namespace App\Services\Pdf\DefaultPdfSpecifications;


use App\Entity\Data\PdfFieldBorder;
use App\Entity\Data\PdfFieldDimensions;
use App\Entity\Data\PdfSpecificationType;
use App\Services\Pdf\PdfSpecification;

// Einzeln 89x36
class PdfLabels12 implements PdfSpecificationInterface
{
    public function getPdfSpecification(): PdfSpecification
    {
        $allBorders = PdfFieldBorder::all()->getValue();
    
        $qrField = new PdfFieldDimensions(
            "lS1Field1",
            16,
            16,
            0,
            0,
            $allBorders
        );
        $mainLocationField = new PdfFieldDimensions(
            "lS1Field2",
            73,
            8,
            16,
            0,
            $allBorders
        );
        $manufacturerNumberField = new PdfFieldDimensions(
            "lS1Field5",
            73,
            8,
            16,
            8,
            $allBorders
        );
        $nameField = new PdfFieldDimensions(
            "lS1Field8",
            89,
            20,
            0,
            16,
            $allBorders
        );
    
        return new PdfSpecification(
            "l_12",
            'Einzeln 89x36',
            89,
            36,
            false,
            false,
            0,
            0,
            1,
            1,
            1,
            [$qrField, $mainLocationField, $manufacturerNumberField, $nameField],
            [],
            [],
            [],
            [],
            ['qr', 'text', 'text', 'text'],
            ['qrCode', 'mainLocationName', 'manufacturerNumber', 'name'],
            ['qr', 'text', 'text', 'text'],
            ['qrCode', 'home', 'barcode', 'name'],
            ['qr', 'text', 'text', 'text'],
            ['qrCode', 'home', 'address', 'name'],
            4,
            0,
            PdfSpecificationType::label(),
            [36, 89],
            'L',
            null,
            true
        );
    }
}
