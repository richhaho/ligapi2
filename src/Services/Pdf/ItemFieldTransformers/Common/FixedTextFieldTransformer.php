<?php

declare(strict_types=1);


namespace App\Services\Pdf\ItemFieldTransformers\Common;


use App\Entity\Data\PdfField;
use App\Services\Pdf\ItemFieldTransformers\ItemFieldTransformerInterface;

class FixedTextFieldTransformer implements ItemFieldTransformerInterface
{
    public function supports(
        string $transformer
    ): bool
    {
        return $transformer === FixedTextFieldTransformer::class;
    }
    
    public function transform(object $entity, PdfField $pdfItemField, int $position): string
    {
        return $pdfItemField->getParams();
    }
}
