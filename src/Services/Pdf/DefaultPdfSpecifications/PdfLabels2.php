<?php

declare(strict_types=1);


namespace App\Services\Pdf\DefaultPdfSpecifications;


use App\Entity\Data\PdfFieldBorder;
use App\Entity\Data\PdfFieldDimensions;
use App\Entity\Data\PdfSpecificationType;
use App\Services\Pdf\PdfSpecification;

// Einzeln 51x19mm
class PdfLabels2 implements PdfSpecificationInterface
{
    public function getPdfSpecification(): PdfSpecification
    {
        $lS2Field1 = new PdfFieldDimensions(
            "lS2Field1",
            17,
            17,
            1,
            1,
            PdfFieldBorder::none()->getValue()
        );
        $lS2Field2 = new PdfFieldDimensions(
            "lS2Field2",
            32,
            $lS2Field1->getHeight(),
            $lS2Field1->getWidth() + $lS2Field1->getX(),
            $lS2Field1->getY(),
            PdfFieldBorder::none()->getValue()
        );
        
        return new PdfSpecification(
            "l_2",
            'Einzeln 51x19mm',
            51,
            19,
            false,
            false,
            0,
            0,
            1,
            1,
            1,
            [$lS2Field1, $lS2Field2],
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
            [51, 19],
            'L',
            null,
            true
        );
    }
}
