<?php

declare(strict_types=1);


namespace App\Services\Pdf\ItemFieldTransformers\MainEntities;


use App\Entity\Data\PdfField;
use App\Entity\MaterialOrderPosition;
use App\Exceptions\Domain\InvalidArgumentException;
use App\Services\Pdf\ItemFieldTransformers\ItemFieldTransformerInterface;

class CustomerNumberFieldTransformer implements ItemFieldTransformerInterface
{
    
    public function supports(
        string $transformer
    ): bool
    {
        return $transformer === CustomerNumberFieldTransformer::class;
    }
    
    public function transform(object $entity, PdfField $pdfItemField, int $position): string
    {
        if ($entity instanceof MaterialOrderPosition) {
            return $entity->getOrderSource()->getSupplier()->getCustomerNumber() ?? '';
        }
        
        throw InvalidArgumentException::forInvalidEntityType(get_class($entity), MaterialOrderPosition::class);
    }
}
