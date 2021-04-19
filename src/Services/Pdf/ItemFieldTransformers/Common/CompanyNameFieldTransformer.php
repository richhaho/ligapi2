<?php

declare(strict_types=1);


namespace App\Services\Pdf\ItemFieldTransformers\Common;


use App\Entity\CompanyAwareInterface;
use App\Entity\Data\PdfField;
use App\Exceptions\Domain\InvalidArgumentException;
use App\Services\Pdf\ItemFieldTransformers\ItemFieldTransformerInterface;

class CompanyNameFieldTransformer implements ItemFieldTransformerInterface
{
    public function supports(
        string $transformer
    ): bool
    {
        return $transformer === CompanyNameFieldTransformer::class;
    }
    
    public function transform(object $entity, PdfField $pdfItemField, int $position): string
    {
        if (!$entity instanceof CompanyAwareInterface) {
            throw InvalidArgumentException::forInvalidEntityType(get_class($entity), CompanyAwareInterface::class);
        }
        return $entity->getCompany()->getName();
    }
}
