<?php

declare(strict_types=1);


namespace App\Services\Pdf\ItemFieldTransformers\MainEntities;


use App\Entity\ConsignmentItem;
use App\Entity\Data\PdfField;
use App\Exceptions\Domain\InvalidArgumentException;
use App\Services\Pdf\ItemFieldTransformers\ItemFieldTransformerInterface;

class DeliveryAddressArrayFieldTransformer implements ItemFieldTransformerInterface
{
    public function supports(
        string $transformer
    ): bool
    {
        return $transformer === DeliveryAddressArrayFieldTransformer::class;
    }
    
    public function transform(object $entity, PdfField $pdfItemField, int $position): string
    {
        if (!$entity instanceof ConsignmentItem) {
            throw InvalidArgumentException::forInvalidEntityType(
                get_class($entity),
                ConsignmentItem::class
            );
        }
        
        return "Anlieferadresse:" . PHP_EOL . $entity->getConsignment()->getDeliveryAddress();
    }
}
