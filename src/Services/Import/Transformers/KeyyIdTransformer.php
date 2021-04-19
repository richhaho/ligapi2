<?php

declare(strict_types=1);


namespace App\Services\Import\Transformers;


use App\Api\Dto\DtoInterface;
use App\Api\Dto\PutKeyy;

class KeyyIdTransformer implements TransformerInterface
{
    public function supports(string $transformer): bool
    {
        return $transformer === KeyyIdTransformer::class;
    }
    
    public function transform(array $data, string $property, DtoInterface $dto, string $title): ?PutKeyy
    {
        if (!$data['keyyId']) {
            return null;
        }
        
        $keyyDto = new PutKeyy();
        $keyyDto->originalId = (string) $data['keyyId'];
        
        return $keyyDto;
    }
}
