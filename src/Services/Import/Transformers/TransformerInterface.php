<?php


namespace App\Services\Import\Transformers;


use App\Api\Dto\DtoInterface;

interface TransformerInterface
{
    public function supports(string $transformer): bool;
    
    public function transform(array $data, string $property, DtoInterface $dto, string $title);
}
