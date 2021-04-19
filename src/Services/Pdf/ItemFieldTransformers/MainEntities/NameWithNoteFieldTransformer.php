<?php

declare(strict_types=1);


namespace App\Services\Pdf\ItemFieldTransformers\MainEntities;


use App\Entity\ConsignmentItem;
use App\Entity\Data\PdfField;
use App\Entity\Keyy;
use App\Entity\Material;
use App\Entity\Tool;
use App\Services\Pdf\ItemFieldTransformers\ItemFieldTransformerInterface;

class NameWithNoteFieldTransformer implements ItemFieldTransformerInterface
{
    
    public function supports(
        string $transformer
    ): bool
    {
        return $transformer === NameWithNoteFieldTransformer::class;
    }
    
    public function transform(object $entity, PdfField $pdfItemField, int $position): string
    {
        if ($entity instanceof ConsignmentItem && $entity->getMaterial()) {
            $entity = $entity->getMaterial();
            return $entity->getName() . ' ' . $entity->getNote();
        }
        if ($entity instanceof ConsignmentItem && $entity->getTool()) {
            $entity = $entity->getTool();
            return $entity->getName() . ' ' . $entity->getNote();
        }
        if ($entity instanceof ConsignmentItem && $entity->getKeyy()) {
            $entity = $entity->getKeyy();
            return $entity->getName() . ' ' . $entity->getNote();
        }
    
        if ($entity instanceof Material || $entity instanceof Tool || $entity instanceof Keyy) {
            return $entity->getName() . ' ' . $entity->getNote();
        }
        
        return "";
    }
}
