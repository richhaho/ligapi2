<?php

declare(strict_types=1);


namespace App\Services\Pdf\ItemFieldTransformers\MainEntities;


use App\Entity\ConsignmentItem;
use App\Entity\Data\PdfField;
use App\Entity\Keyy;
use App\Entity\Material;
use App\Entity\Tool;
use App\Exceptions\Domain\InvalidArgumentException;
use App\Services\Pdf\ItemFieldTransformers\ItemFieldTransformerInterface;

class OwnerFieldTransformer implements ItemFieldTransformerInterface
{
    public function supports(
        string $transformer
    ): bool
    {
        return $transformer === OwnerFieldTransformer::class;
    }
    
    public function transform(object $entity, PdfField $pdfItemField, int $position): string
    {
        if ($entity instanceof ConsignmentItem && $entity->getTool()) {
            $entity = $entity->getTool();
        }
        if ($entity instanceof ConsignmentItem && $entity->getKeyy()) {
            $entity = $entity->getKeyy();
        }
        if (!$entity instanceof Tool && !$entity instanceof Keyy) {
            throw InvalidArgumentException::forInvalidEntityType(get_class($entity), Tool::class . '/' . Keyy::class);
        }
        
        return $entity->getOwner();
    }
}
