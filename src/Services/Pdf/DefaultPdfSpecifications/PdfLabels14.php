<?php

declare(strict_types=1);


namespace App\Services\Pdf\DefaultPdfSpecifications;


use App\Entity\Data\PdfFieldBorder;
use App\Entity\Data\PdfFieldDimensions;
use App\Entity\Data\PdfSpecificationType;
use App\Services\Pdf\PdfSpecification;

// 2 x 13x25mm
class PdfLabels14 implements PdfSpecificationInterface
{
    public function getPdfSpecification(): PdfSpecification
    {
        $qrField = new PdfFieldDimensions(
            "lS2Field1",
            11,
            11,
            1,
            1,
            PdfFieldBorder::none()->getValue()
        );
        $nameField = new PdfFieldDimensions(
            "lS2Field2",
            12,
            11,
            10.5,
            1,
            PdfFieldBorder::none()->getValue()
        );
        
        return new PdfSpecification(
            "l_14",
            '2 x 25x13mm',
            25,
            13,
            false,
            false,
            0,
            0,
            1,
            2,
            1,
            [$qrField, $nameField],
            [],
            [],
            [],
            [],
            ['qr', 'text'],
            ['qrCode', 'name'],
            ['qr', 'text'],
            ['qrCode', 'name'],
            ['qr', 'text'],
            ['qrCode', 'name'],
            2,
            0,
            PdfSpecificationType::label(),
            [25, 26],
            'L',
            null,
            true
        );
    }
}
