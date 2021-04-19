<?php

declare(strict_types=1);


namespace App\Entity\Data;


class PdfFieldDimensions
{
    private string $description;
    private float $width;
    private float $height;
    private float $x;
    private float $y;
    private PdfFieldBorder $border;
    private int $fontSize;
    private PdfAlignment $alignment;
    private string $format;
    private float $marginAfterwards;
    private bool $startContentAfterwards;
    private bool $skipEmpty;
    
    public function __construct(
        string $description,
        ?float $width = 0,
        ?float $height = 0,
        ?float $x = 0,
        ?float $y = 0,
        ?string $border = 'none',
        ?int $fontSize = 10,
        ?string $alignment = 'left',
        ?string $format = '',
        ?float $marginAfterwards = null,
        ?bool $startContentAfterwards = false,
        ?bool $skipEmpty = false
    )
    {
        $this->description = $description;
        $this->width = $width ?? 0;
        $this->height = $height ?? 0;
        $this->x = $x ?? 0;
        $this->y = $y ?? 0;
        $this->border = PdfFieldBorder::fromString($border ?? 'none');
        $this->fontSize = $fontSize ?? 10;
        $this->alignment = PdfAlignment::fromString($alignment ?? 'left');
        $this->format = $format ?? '';
        $this->marginAfterwards = $marginAfterwards ?? (($this->fontSize / 2.835) + 1.4);
        $this->startContentAfterwards = !!$startContentAfterwards;
        $this->skipEmpty = !!$skipEmpty;
    }
    
    public function getDescription(): string
    {
        return $this->description;
    }
    
    public function getWidth(): float
    {
        return $this->width;
    }
    
    public function getHeight(): float
    {
        return $this->height;
    }
    
    public function getX(): float
    {
        return $this->x;
    }
    
    public function getY(): float
    {
        return $this->y;
    }
    
    public function getBorder(): PdfFieldBorder
    {
        return $this->border;
    }
    
    public function getFontSize(): int
    {
        return $this->fontSize;
    }
    
    public function getAlignment(): PdfAlignment
    {
        return $this->alignment;
    }
    
    public function getFormat(): string
    {
        return $this->format;
    }
    
    public function getMarginAfterwards(): float
    {
        return $this->marginAfterwards;
    }
    
    public function isStartContentAfterwards(): bool
    {
        return $this->startContentAfterwards;
    }
    
    public function isSkipEmpty(): bool
    {
        return $this->skipEmpty;
    }
    
    public function toArray(): array
    {
        return [
            'description' => $this->description,
            'width' => $this->width,
            'height' => $this->height,
            'x' => $this->x,
            'y' => $this->y,
            'border' => $this->border->getValue(),
            'fontSize' => $this->fontSize,
            'alignment' => $this->alignment->getValue(),
            'format' => $this->format,
            'marginAfterwards' => $this->marginAfterwards,
            'startContentAfterwards' => $this->startContentAfterwards,
            'skipEmpty' => $this->skipEmpty
        ];
    }
    
    public static function fromArray(array $labelFieldDimensions): self
    {
        return new self(
            $labelFieldDimensions['description'],
            $labelFieldDimensions['width'],
            $labelFieldDimensions['height'],
            $labelFieldDimensions['x'],
            $labelFieldDimensions['y'],
            $labelFieldDimensions['border'],
            $labelFieldDimensions['fontSize'],
            $labelFieldDimensions['alignment'],
            $labelFieldDimensions['format'],
            $labelFieldDimensions['marginAfterwards'],
            $labelFieldDimensions['startContentAfterwards'],
            $labelFieldDimensions['skipEmpty']
        );
    }
}
