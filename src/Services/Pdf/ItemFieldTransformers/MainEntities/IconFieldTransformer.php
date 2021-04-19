<?php

declare(strict_types=1);


namespace App\Services\Pdf\ItemFieldTransformers\MainEntities;


use App\Entity\ConsignmentItem;
use App\Entity\Data\PdfField;
use App\Entity\Keyy;
use App\Entity\Material;
use App\Entity\Tool;
use App\Services\Pdf\ItemFieldTransformers\ItemFieldTransformerInterface;

class IconFieldTransformer implements ItemFieldTransformerInterface
{
    public function supports(
        string $transformer
    ): bool
    {
        return $transformer === IconFieldTransformer::class;
    }
    
    public function transform(object $entity, PdfField $pdfItemField, int $position): string
    {
        if (($entity instanceof ConsignmentItem && $entity->getMaterial()) || $entity instanceof Material) {
            return 'material.png';
        }
        if (($entity instanceof ConsignmentItem && $entity->getTool()) || $entity instanceof Tool) {
            return 'tool.png';
        }
        if (($entity instanceof ConsignmentItem && $entity->getKeyy()) || $entity instanceof Keyy) {
            return 'keyy.png';
        }
        return '';
    }
}
