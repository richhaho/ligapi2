<?php

declare(strict_types=1);


namespace App\Services\Pdf\ItemFieldTransformers\Common;


use App\Entity\Data\PdfField;
use App\Entity\MaterialOrderPosition;
use App\Exceptions\Domain\InvalidArgumentException;
use App\Services\Pdf\ItemFieldTransformers\ItemFieldTransformerInterface;

class SupplierAddressFieldTransformer implements ItemFieldTransformerInterface
{
    public function supports(
        string $transformer
    ): bool
    {
        return $transformer === SupplierAddressFieldTransformer::class;
    }
    
    public function transform(object $entity, PdfField $pdfItemField, int $position): array
    {
        if (get_class($entity) !== MaterialOrderPosition::class) {
            throw InvalidArgumentException::forInvalidEntityType(get_class($entity), MaterialOrderPosition::class);
        }
        return $entity->getOrderSource()->getSupplier()->getAddressArray();
    }
}
