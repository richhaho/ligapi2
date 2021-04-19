<?php

declare(strict_types=1);


namespace App\Services\Pdf\DefaultPdfSpecifications;


use App\Entity\Data\PdfFieldBorder;
use App\Entity\Data\PdfFieldDimensions;
use App\Entity\Data\PdfSpecificationType;
use App\Services\Pdf\PdfSpecification;

// DinA4 148x105mm
class PdfLabels3 implements PdfSpecificationInterface
{
    public function getPdfSpecification(): PdfSpecification
    {
        $imageField = new PdfFieldDimensions(
            "imageField",
            49.5,
            49.5,
            95.5,
            3,
            PdfFieldBorder::none()->getValue()
        );
        $titleField = new PdfFieldDimensions(
            "titleField",
            90.5,
            10,
            5,
            5,
            PdfFieldBorder::none()->getValue(),
            14,
            null,
            'B'
        );
        $articleInfoField = new PdfFieldDimensions(
            "articleInfoField",
            90.5,
            10,
            5,
            11,
            PdfFieldBorder::none()->getValue(),
            14,
            null,
            'B'
        );
        $nameField = new PdfFieldDimensions(
            "nameField",
            90.5,
            85,
            5,
            19,
            PdfFieldBorder::none()->getValue(),
            14
        );
        $qrField = new PdfFieldDimensions(
            "qrField",
            47.5,
            47.5,
            97.5,
            54.5,
            PdfFieldBorder::none()->getValue()
        );
        
        return new PdfSpecification(
            "l_3",
            'A4 148x105mm',
            148,
            105,
            false,
            false,
            0,
            0,
            2,
            2,
            1,
            [
                $imageField,
                $titleField,
                $articleInfoField,
                $nameField,
                $qrField
            ],
            [],
            [],
            [],
            [],
            ['image', 'text', 'text', 'text', 'qr'],
            ['profileImage', 'fixedText', 'unitWithManufacturerNumber', 'nameWithNote', 'qrCode'],
            ['image', 'text', 'text', 'text', 'qr'],
            ['profileImage','fixedText', 'itemNumber',  'nameWithNote', 'qrCode'],
            ['image', 'text', 'text', 'text', 'qr'],
            ['profileImage', 'fixedText', 'itemNumber', 'nameWithNote', 'qrCode'],
            5,
            0,
            PdfSpecificationType::label(),
            null,
            'L',
            null,
            true
        );
    }
}
