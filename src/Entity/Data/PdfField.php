<?php

declare(strict_types=1);


namespace App\Entity\Data;



class PdfField
{
    private string $property;       // From pdfItemFormats.yaml
    private string $type;           // Text, image, qr
    private string $params;         // content, customField id etc.
    private int $position;          // Where to put on the label

    public function __construct(
        string $property,
        string $type,
        string $params,
        int $position
    )
    {
        $this->property = $property;
        $this->type = $type;
        $this->params = $params;
        $this->position = $position;
    }
    
    public function getProperty(): string
    {
        return $this->property;
    }
    
    public function getType(): string
    {
        return $this->type;
    }
    
    public function getParams(): string
    {
        return $this->params;
    }
    
    public function setParams(string $params)
    {
        $this->params = $params;
    }
    
    public function getPosition(): int
    {
        return $this->position;
    }
    
    public function toArray(): array
    {
        return [
            'property' => $this->property,
            'type' => $this->type,
            'params' => $this->params,
            'position' => $this->position
        ];
    }
    
    public static function fromArray(array $labelFieldArray): self
    {
        return new self(
            $labelFieldArray['property'] ?? "",
            $labelFieldArray['type'],
            $labelFieldArray['params'],
            $labelFieldArray['position']
        );
    }
}
