<?php

declare(strict_types=1);


namespace App\Services\Pdf\ItemFieldTransformers\MainEntities;


use App\Entity\ConsignmentItem;
use App\Entity\Data\PdfField;
use App\Entity\Keyy;
use App\Entity\Material;
use App\Entity\Tool;
use App\Services\Pdf\ItemFieldTransformers\ItemFieldTransformerInterface;

class QrCodeFieldTransformer implements ItemFieldTransformerInterface
{
    public function supports(
        string $transformer
    ): bool
    {
        return $transformer === QrCodeFieldTransformer::class;
    }
    
    public function transform(object $entity, PdfField $pdfItemField, int $position): string
    {
        if ($entity instanceof ConsignmentItem && $entity->getMaterial()) {
            $entity = $entity->getMaterial();
        }
        if ($entity instanceof ConsignmentItem && $entity->getTool()) {
            $entity = $entity->getTool();
        }
        if ($entity instanceof ConsignmentItem && $entity->getKeyy()) {
            $entity = $entity->getKeyy();
        }
        if ($entity instanceof Material) {
            return "M|" . $entity->getItemNumber();
        }
        if ($entity instanceof Tool) {
            return "T|" . $entity->getItemNumber();
        }
        if ($entity instanceof Keyy) {
            return "K|" . $entity->getItemNumber();
        }
        return '';
    }
}
