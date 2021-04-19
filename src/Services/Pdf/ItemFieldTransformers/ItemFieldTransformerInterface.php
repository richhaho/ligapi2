<?php

declare(strict_types=1);


namespace App\Services\Pdf\ItemFieldTransformers;


use App\Entity\Data\PdfField;

interface ItemFieldTransformerInterface
{
    public function supports(string $transformer): bool;
    public function transform(object $entity, PdfField $pdfItemField, int $position);
}
