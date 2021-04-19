<?php

declare(strict_types=1);


namespace App\Services\Pdf\ItemFieldTransformers\MainEntities;


use App\Entity\ConsignmentItem;
use App\Entity\Data\PdfField;
use App\Entity\Material;
use App\Entity\Tool;
use App\Services\Pdf\ItemFieldTransformers\ItemFieldTransformerInterface;

class ItemGroupFieldTransformer implements ItemFieldTransformerInterface
{
    public function supports(
        string $transformer
    ): bool
    {
        return $transformer === ItemGroupFieldTransformer::class;
    }
    
    public function transform(object $entity, PdfField $pdfItemField, int $position): string
    {
        if ($entity instanceof ConsignmentItem && $entity->getMaterial()) {
            $entity = $entity->getMaterial();
        }
        if ($entity instanceof ConsignmentItem && $entity->getTool()) {
            $entity = $entity->getTool();
        }
        if ($entity instanceof Material || $entity instanceof Tool) {
            return $entity->getItemGroup() ? $entity->getItemGroup()->getName() : '';
        }
        return '';
    }
}
