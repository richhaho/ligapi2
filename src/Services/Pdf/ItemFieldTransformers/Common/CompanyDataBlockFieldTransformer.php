<?php

declare(strict_types=1);


namespace App\Services\Pdf\ItemFieldTransformers\Common;


use App\Entity\CompanyAwareInterface;
use App\Entity\Data\PdfField;
use App\Exceptions\Domain\InvalidArgumentException;
use App\Services\Pdf\ItemFieldTransformers\ItemFieldTransformerInterface;

class CompanyDataBlockFieldTransformer implements ItemFieldTransformerInterface
{
    public function supports(
        string $transformer
    ): bool
    {
        return $transformer === CompanyDataBlockFieldTransformer::class;
    }
    
    public function transform(object $entity, PdfField $pdfItemField, int $position): array
    {
        if (!$entity instanceof CompanyAwareInterface) {
            throw InvalidArgumentException::forInvalidEntityType(get_class($entity), CompanyAwareInterface::class);
        }
        $company = $entity->getCompany();
        return $company->getAddressArray();
    }
}
