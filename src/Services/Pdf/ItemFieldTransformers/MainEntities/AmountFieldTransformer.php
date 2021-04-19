<?php

declare(strict_types=1);


namespace App\Services\Pdf\ItemFieldTransformers\MainEntities;


use App\Entity\ConsignmentItem;
use App\Entity\Data\PdfField;
use App\Entity\Keyy;
use App\Entity\MaterialOrderPosition;
use App\Exceptions\Domain\InvalidArgumentException;
use App\Services\Pdf\ItemFieldTransformers\ItemFieldTransformerInterface;

class AmountFieldTransformer implements ItemFieldTransformerInterface
{
    
    public function supports(
        string $transformer
    ): bool
    {
        return $transformer === AmountFieldTransformer::class;
    }
    
    public function transform(object $entity, PdfField $pdfItemField, int $position): string
    {
        if ($entity instanceof Keyy) {
            return (string) $entity->getAmount();
        }
        if ($entity instanceof MaterialOrderPosition) {
            return (string) $entity->getAmount();
        }
        if ($entity instanceof ConsignmentItem) {
            return (string) $entity->getAmount();
        }
        
        throw InvalidArgumentException::forInvalidEntityType(get_class($entity), MaterialOrderPosition::class . ', ' . Keyy::class . ', ' . ConsignmentItem::class);
    }
}
