<?php

declare(strict_types=1);


namespace App\Services\Pdf\ItemFieldTransformers\MainEntities;


use App\Entity\ConsignmentItem;
use App\Entity\Data\PdfField;
use App\Entity\Material;
use App\Services\Pdf\ItemFieldTransformers\ItemFieldTransformerInterface;

class CategoryFieldTransformer implements ItemFieldTransformerInterface
{
    public function supports(
        string $transformer
    ): bool
    {
        return $transformer === CategoryFieldTransformer::class;
    }
    
    public function transform(object $entity, PdfField $pdfItemField, int $position): string
    {
        if ($entity instanceof ConsignmentItem && $entity->getMaterial()) {
            $entity = $entity->getMaterial();
        }
        if (!$entity instanceof Material) {
            return "";
        }
        
        if ($entity->isPermanentInventory() && $entity->getMainLocationLinkMinStock()) {
            return 'M-B: ' . $entity->getMainLocationLinkMinStock();
        }
    
        if (!$entity->isPermanentInventory() && $entity->getMainLocationLinkMinStock()) {
            return 'Min: ' . $entity->getMainLocationLinkMinStock();
        }
    
        if ($entity->isPermanentInventory() && !$entity->getMainLocationLinkMinStock()) {
            return 'Buchen';
        }
        
        return 'Fest';
    }
}
