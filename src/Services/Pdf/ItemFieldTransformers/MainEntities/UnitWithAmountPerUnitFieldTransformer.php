<?php

declare(strict_types=1);


namespace App\Services\Pdf\ItemFieldTransformers\MainEntities;


use App\Entity\Data\PdfField;
use App\Entity\MaterialOrderPosition;
use App\Exceptions\Domain\InvalidArgumentException;
use App\Services\Pdf\ItemFieldTransformers\ItemFieldTransformerInterface;

class UnitWithAmountPerUnitFieldTransformer implements ItemFieldTransformerInterface
{
    
    public function supports(
        string $transformer
    ): bool
    {
        return $transformer === UnitWithAmountPerUnitFieldTransformer::class;
    }
    
    public function transform(object $entity, PdfField $pdfItemField, int $position): string
    {
        if ($entity instanceof MaterialOrderPosition) {
            $amountPerPuchaseUnit = $entity->getOrderSource()->getAmountPerPurchaseUnit() ?? 1;
            $unit = $entity->getMaterial()->getUnit() ?? 'Stk.';
            return $amountPerPuchaseUnit . ' ' . $unit;
        }
        
        throw InvalidArgumentException::forInvalidEntityType(get_class($entity), MaterialOrderPosition::class);
    }
}
