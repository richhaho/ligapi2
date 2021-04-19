<?php

declare(strict_types=1);


namespace App\Services\Pdf\ItemFieldTransformers\MainEntities;


use App\Entity\ConsignmentItem;
use App\Entity\Data\PdfField;
use App\Entity\Material;
use App\Exceptions\Domain\InvalidArgumentException;
use App\Services\Pdf\ItemFieldTransformers\ItemFieldTransformerInterface;

class MainLocationNameFieldTransformer implements ItemFieldTransformerInterface
{
    
    public function supports(
        string $transformer
    ): bool
    {
        return $transformer === MainLocationNameFieldTransformer::class;
    }
    
    public function transform(object $entity, PdfField $pdfItemField, int $position): string
    {
        if ($entity instanceof Material) {
            return $entity->getMainLocationLinkName();
        }
        if ($entity instanceof ConsignmentItem) {
            if ($entity->getMaterial()) {
                return $entity->getMaterial()->getMainLocationLinkName();
            }
            if ($entity->getTool()) {
                return $entity->getTool()->getHome();
            }
            if ($entity->getKeyy()) {
                return $entity->getKeyy()->getHome();
            }
            return '';
        }
    
        throw InvalidArgumentException::forInvalidEntityType(get_class($entity), Material::class . ', ' . ConsignmentItem::class);
    }
}
