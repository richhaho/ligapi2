<?php

declare(strict_types=1);


namespace App\Entity\Data;


use App\Api\Dto\FileDto;

class File
{
    private string $relativePath;
    private string $displayedName;
    private string $docType;
    private string $mimeType;
    private int $size;
    private ?int $width;
    private ?int $height;
    private ?string $originalPath;

    public function __construct(
        string $relativePath,
        string $displayedName,
        string $mimeType,
        int $size,
        string $docType,
        ?int $width = null,
        ?int $height = null,
        ?string $originalPath = null
    )
    {
        $this->displayedName = $displayedName;
        $this->relativePath = $relativePath;
        $this->mimeType = $mimeType;
        $this->size = $size;
        $this->docType = $docType;
        $this->height = $height;
        $this->width = $width;
        $this->originalPath = $originalPath;
    }
    
    public function getDisplayedName(): string
    {
        return $this->displayedName;
    }
    
    public function setDisplayedName(string $displayedName): void
    {
        $this->displayedName = $displayedName;
    }
    
    public function getRelativePath(): string
    {
        return $this->relativePath;
    }
    
    public function getMimeType(): string
    {
        return $this->mimeType;
    }
    
    public function getHeight(): ?int
    {
        return $this->height;
    }
    
    public function getOriginalPath(): ?string
    {
        return $this->originalPath;
    }
    
    public function getWidth(): ?int
    {
        return $this->width;
    }
    
    public function getSize(): int
    {
        return $this->size;
    }
    
    public function getDocType(): string
    {
        return $this->docType;
    }
    
    public function setDocType(string $docType): void
    {
        $this->docType = $docType;
    }
    
    public function toArray(): array
    {
        return [
            'relativePath' => $this->relativePath,
            'displayedName' => $this->displayedName,
            'docType' => $this->docType,
            'mimeType' => $this->mimeType,
            'height' => $this->height,
            'width' => $this->width,
            'size' => $this->size,
            'originalPath' => $this->originalPath
        ];
    }
    
    public static function fromDto(FileDto $fileDto): self
    {
        $file = new self(
            $fileDto->relativePath,
            $fileDto->displayedName,
            $fileDto->mimeType,
            $fileDto->size,
            $fileDto->docType
        );
        if (isset($fileDto->originalPath)) {
            $file->originalPath = $fileDto->originalPath;
        }
        if (isset($fileDto->height)) {
            $file->height = $fileDto->height;
        }
        if (isset($fileDto->width)) {
            $file->width = $fileDto->width;
        }
    
        return $file;
    }
    
    public static function fromArray(array $fileArray): self
    {
        $file = new self(
            $fileArray['relativePath'],
            $fileArray['displayedName'],
            $fileArray['mimeType'],
            $fileArray['size'],
            $fileArray['docType']
        );
        if (isset($fileArray['originalPath'])) {
            $file->originalPath = $fileArray['originalPath'];
        }
        if (isset($fileArray['height'])) {
            $file->height = $fileArray['height'];
        }
        if (isset($fileArray['width'])) {
            $file->width = $fileArray['width'];
        }
        
        return $file;
    }

}
