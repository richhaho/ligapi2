<?php

declare(strict_types=1);


namespace App\Services\Pdf\ItemFieldTransformers\Common;


use App\Entity\Data\PdfField;
use App\Entity\MaterialOrderPosition;
use App\Exceptions\Domain\InvalidArgumentException;
use App\Services\Pdf\ItemFieldTransformers\ItemFieldTransformerInterface;

class SalutationFieldTransformer implements ItemFieldTransformerInterface
{
    public function supports(
        string $transformer
    ): bool
    {
        return $transformer === SalutationFieldTransformer::class;
    }
    
    public function transform(object $entity, PdfField $pdfItemField, int $position): string
    {
        if (get_class($entity) !== MaterialOrderPosition::class) {
            throw InvalidArgumentException::forInvalidEntityType(get_class($entity), MaterialOrderPosition::class);
        }
        return $entity->getOrderSource()->getSupplier()->getEmailSalutation() . ',' ?? "Sehr geehrte Damen und Herren,";
    }
}
