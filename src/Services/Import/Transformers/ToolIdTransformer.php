<?php

declare(strict_types=1);


namespace App\Services\Import\Transformers;


use App\Api\Dto\DtoInterface;
use App\Api\Dto\PutTool;

class ToolIdTransformer implements TransformerInterface
{
    public function supports(string $transformer): bool
    {
        return $transformer === ToolIdTransformer::class;
    }
    
    public function transform(array $data, string $property, DtoInterface $dto, string $title): ?PutTool
    {
        if (!$data['toolId']) {
            return null;
        }
        
        $toolDto = new PutTool();
        $toolDto->originalId = (string) $data['toolId'];
        
        return $toolDto;
    }
}
