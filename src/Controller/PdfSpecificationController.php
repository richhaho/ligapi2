<?php

declare(strict_types=1);


namespace App\Controller;


use App\Api\ApiContext;
use App\Exceptions\Domain\MissingDataException;
use App\Services\Pdf\DefaultPdfSpecifications\PdfSpecificationInterface;
use App\Services\Pdf\PdfSpecification;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/api/pdfdocumentspecifications/", name="api_labelSpecification_")
 */
class PdfSpecificationController
{
    /**
     * @Route(path="fieldparameteroptions", name="field_parameter_options", methods={"GET"})
     */
    public function get_field_parameter_options(array $pdfItemFormatMappings): array
    {
        return $pdfItemFormatMappings;
    }
    
    /**
     * @Route(name="index", methods={"GET"})
     * @ApiContext(groups={"list"}, selfRoute="api_labelSpecification_get")
     */
    public function index(iterable $pdfSpecifications): iterable
    {
        $pdfSpecs = [];
        /** @var PdfSpecificationInterface $pdfSpecification */
        foreach ($pdfSpecifications as $pdfSpecification) {
            $pdfSpecs[] = $pdfSpecification->getPdfSpecification();
        }
        return $pdfSpecs;
    }
    
    /**
     * @Route(path="{id}", name="get", methods={"GET"})
     * @ApiContext(groups={"detail"}, selfRoute="api_labelSpecification_get")
     */
    public function get(string $id, iterable $pdfSpecifications): PdfSpecification
    {
        /** @var PdfSpecification $pdfSpecification */
        foreach ($pdfSpecifications as $pdfSpecification) {
            if ($pdfSpecification->getId() === $id) {
                return $pdfSpecification;
            }
        }
        throw MissingDataException::forEntityNotFound($id, PdfSpecification::class);
    }
}
