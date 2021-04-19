<?php

declare(strict_types=1);


namespace App\Services\Pdf\ItemFieldTransformers\MainEntities;


use App\Entity\ConsignmentItem;
use App\Entity\Data\PdfField;
use App\Entity\MaterialOrderPosition;
use App\Exceptions\Domain\InvalidArgumentException;
use App\Services\Pdf\ItemFieldTransformers\ItemFieldTransformerInterface;

class ConsignmentNumberFieldTransformer implements ItemFieldTransformerInterface
{
    public function supports(
        string $transformer
    ): bool
    {
        return $transformer === ConsignmentNumberFieldTransformer::class;
    }
    
    public function transform(object $entity, PdfField $pdfItemField, int $position): string
    {
        if ($entity instanceof MaterialOrderPosition) {
            return $entity->getMaterialOrder()->getConsignmentNumber() ?? '';
        }
        if ($entity instanceof ConsignmentItem) {
            return (string) $entity->getConsignment()->getConsignmentNumber() ?? '';
        }
        
        throw InvalidArgumentException::forInvalidEntityType(get_class($entity), MaterialOrderPosition::class . '|' . ConsignmentItem::class);
    }
}
