<?php

declare(strict_types=1);


namespace App\Services\Pdf\ItemFieldTransformers\Common;


use App\Entity\Data\PdfField;
use App\Services\Pdf\ItemFieldTransformers\ItemFieldTransformerInterface;
use DateTimeImmutable;

class CurrentDateTimeFieldTransformer implements ItemFieldTransformerInterface
{
    public function supports(
        string $transformer
    ): bool
    {
        return $transformer === CurrentDateTimeFieldTransformer::class;
    }
    
    public function transform(object $entity, PdfField $pdfItemField, int $position): string
    {
        return (new DateTimeImmutable())->format('d.m.Y H:i:s');
    }
}
