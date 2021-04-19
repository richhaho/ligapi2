<?php

declare(strict_types=1);


namespace App\Services\Pdf\ItemFieldTransformers\MainEntities;


use App\Entity\ConsignmentItem;
use App\Entity\Data\PdfField;
use App\Entity\Material;
use App\Services\Pdf\ItemFieldTransformers\ItemFieldTransformerInterface;

class Prio1SupplierNameFieldTransformer implements ItemFieldTransformerInterface
{
    
    public function supports(
        string $transformer
    ): bool
    {
        return $transformer === Prio1SupplierNameFieldTransformer::class;
    }
    
    public function transform(object $entity, PdfField $pdfItemField, int $position): string
    {
        if ($entity instanceof ConsignmentItem && $entity->getMaterial()) {
            $entity = $entity->getMaterial();
        }
        if (!$entity instanceof Material) {
            return "";
        }
        
        return $entity->getMainOrderSource() ? $entity->getMainOrderSource()->getSupplier()->getName() : '';
    }
}
