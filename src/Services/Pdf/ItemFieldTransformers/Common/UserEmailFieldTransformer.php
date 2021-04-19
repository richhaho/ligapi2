<?php

declare(strict_types=1);


namespace App\Services\Pdf\ItemFieldTransformers\Common;


use App\Entity\Data\PdfField;
use App\Services\CurrentUserProvider;
use App\Services\Pdf\ItemFieldTransformers\ItemFieldTransformerInterface;

class UserEmailFieldTransformer implements ItemFieldTransformerInterface
{
    private CurrentUserProvider $currentUserProvider;
    
    public function __construct(CurrentUserProvider $currentUserProvider)
    {
        $this->currentUserProvider = $currentUserProvider;
    }
    
    public function supports(
        string $transformer
    ): bool
    {
        return $transformer === UserEmailFieldTransformer::class;
    }
    
    public function transform(object $entity, PdfField $pdfItemField, int $position): string
    {
        return $this->currentUserProvider->getAuthenticatedUser()->getEmail();
    }
}
