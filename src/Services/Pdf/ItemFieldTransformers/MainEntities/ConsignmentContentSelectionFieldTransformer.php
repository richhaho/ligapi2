<?php

declare(strict_types=1);


namespace App\Services\Pdf\ItemFieldTransformers\MainEntities;


use App\Entity\Data\PdfField;
use App\Services\Pdf\ItemFieldTransformers\ItemFieldTransformerInterface;

class ConsignmentContentSelectionFieldTransformer implements ItemFieldTransformerInterface
{
    
    public function supports(
        string $transformer
    ): bool
    {
        return $transformer === ConsignmentContentSelectionFieldTransformer::class;
    }
    
    public function transform(object $entity, PdfField $pdfItemField, int $position): array
    {
        return [
            '_ Kommissionierung',
            '_ Reinigung',
            '_ Reparatur',
            '_ Mängelaufnahme/Dokumentation',
            '_ Verpackung für Versand',
            '_ Versand durch:'
        ];
    }
}
