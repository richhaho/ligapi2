<?php

declare(strict_types=1);


namespace App\Api\Dto;


class FileDto implements DtoInterface
{
    public ?string $displayedName = null;
    public string $docType;
    public string $mimeType;
    
    public ?string $relativePath = null;
    public ?string $originalPath = null;
    
    public ?PutMaterialDto $material = null;
    public ?PutTool $tool = null;
    public ?PutKeyy $keyy = null;
    
    public ?int $size = null;
    public ?int $height = null;
    public ?int $width = null;
}
