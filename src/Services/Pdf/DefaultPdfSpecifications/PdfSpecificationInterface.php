<?php


namespace App\Services\Pdf\DefaultPdfSpecifications;



use App\Services\Pdf\PdfSpecification;

interface PdfSpecificationInterface
{
    public function getPdfSpecification(): PdfSpecification;
}
