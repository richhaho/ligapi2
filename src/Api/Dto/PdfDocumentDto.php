<?php

declare(strict_types=1);


namespace App\Api\Dto;


class PdfDocumentDto
{
    public array $ids;
    public ?array $pdfDocumentTypeIds = null;
    public ?string $entityType = null;
}
