<?php

declare(strict_types=1);


namespace App\Services\Pdf\DefaultPdfSpecifications;


use App\Entity\Data\PdfAlignment;
use App\Entity\Data\PdfFieldBorder;
use App\Entity\Data\PdfFieldDimensions;
use App\Entity\Data\PdfSpecificationType;
use App\Services\Pdf\PdfSpecification;

// DinA4
class PdfLabels13 implements PdfSpecificationInterface
{
    public function getPdfSpecification(): PdfSpecification
    {
        $nameField = new PdfFieldDimensions(
            "nameField",
            130,
            120,
            10,
            10,
            PdfFieldBorder::none()->getValue()
        );
        $imageField = new PdfFieldDimensions(
            "imageField",
            130,
            190,
            150,
            10,
            PdfFieldBorder::none()->getValue(),
            null,
            PdfAlignment::center()->getValue()
        );
        $qrField = new PdfFieldDimensions(
            "qrField",
            80,
            80,
            35,
            120,
            PdfFieldBorder::none()->getValue()
        );
        
        return new PdfSpecification(
            "l_13",
            'A4',
            210,
            297,
            false,
            false,
            0,
            0,
            1,
            1,
            1,
            [
                $nameField,
                $qrField,
                $imageField
            ],
            [],
            [],
            [],
            [],
            ['text', 'qr', 'image'],
            ['name', 'qrCode', 'profileImage'],
            ['text', 'qr', 'image'],
            ['name', 'qrCode', 'profileImage'],
            ['text', 'qr', 'image'],
            ['name', 'qrCode', 'profileImage'],
            3,
            0,
            PdfSpecificationType::label(),
            null,
            'L',
            null,
            true
        );
    }
}
