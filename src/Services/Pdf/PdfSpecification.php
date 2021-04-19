<?php

declare(strict_types=1);


namespace App\Services\Pdf;


use App\Entity\Data\PdfField;
use App\Entity\Data\PdfFieldDimensions;
use App\Entity\Data\PdfSpecificationType;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

class PdfSpecification
{
    /**
     * @Groups({"detail", "list"})
     */
    private string $id;
    
    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private DateTimeImmutable $createdAt;
    
    /**
     * @ORM\Column(type="string")
     * @Groups({"detail", "list"})
     */
    private string $pageSize;
    
    /**
     * @ORM\Column(type="string")
     * @Groups({"detail", "list"})
     */
    private string $orientation;
    
    /**
     * @ORM\Column(type="float")
     * @Groups({"detail", "list"})
     */
    private float $labelWidth;
    
    /**
     * @ORM\Column(type="float")
     * @Groups({"detail", "list"})
     */
    private float $labelHeight;
    
    /**
     * @ORM\Column(type="boolean")
     * @Groups({"detail", "list"})
     */
    private bool $printHeader;
    
    /**
     * @ORM\Column(type="boolean")
     * @Groups({"detail", "list"})
     */
    private bool $printFooter;
    
    /**
     * @ORM\Column(type="boolean")
     * @Groups({"detail", "list"})
     */
    private bool $autoIncreaseFontSize;
    
    /**
     * @ORM\Column(type="float")
     * @Groups({"detail", "list"})
     */
    private float $labelStartX;
    
    /**
     * @ORM\Column(type="float")
     * @Groups({"detail", "list"})
     */
    private float $labelStartY;
    
    /**
     * @ORM\Column(type="integer")
     * @Groups({"detail", "list"})
     */
    private int $labelsPerColumn;
    
    /**
     * @ORM\Column(type="integer")
     * @Groups({"detail", "list"})
     */
    private int $rowsPerPage;
    
    /**
     * @ORM\Column(type="integer")
     * @Groups({"detail", "list"})
     */
    private int $copies;
    
    /**
     * @ORM\Column(type="string")
     * @Groups({"detail", "list"})
     */
    private string $name;
    
    /**
     * @ORM\Column(type="json")
     * @Groups({"detail", "list"})
     */
    private array $itemFieldDimensions;
    
    /**
     * @ORM\Column(type="json")
     * @Groups({"detail", "list"})
     */
    private array $commonFieldDimensions;
    
    /**
     * @ORM\Column(type="integer")
     * @Groups({"detail", "list"})
     */
    private int $itemFieldsCount;
    
    /**
     * @ORM\Column(type="integer")
     * @Groups({"detail", "list"})
     */
    private int $commonFieldsCount;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"detail", "list"})
     */
    private ?string $backgroundImage;
    
    /**
     * @ORM\Column(type="json", nullable=true)
     * @Groups({"detail", "list"})
     */
    private ?array $defaultCommonFieldTypes;
    
    /**
     * @ORM\Column(type="json", nullable=true)
     * @Groups({"detail", "list"})
     */
    private ?array $defaultCommonFieldParams;
    
    /**
     * @ORM\Column(type="json", nullable=true)
     * @Groups({"detail", "list"})
     */
    private ?array $defaultFieldTypesMaterial;
    
    /**
     * @ORM\Column(type="json", nullable=true)
     * @Groups({"detail", "list"})
     */
    private ?array $defaultFieldTypesTool;
    
    /**
     * @ORM\Column(type="json", nullable=true)
     * @Groups({"detail", "list"})
     */
    private ?array $defaultFieldTypesKeyy;
    
    /**
     * @ORM\Column(type="json", nullable=true)
     * @Groups({"detail", "list"})
     */
    private ?array $defaultCommonFieldProperties;
    
    /**
     * @ORM\Column(type="json", nullable=true)
     * @Groups({"detail", "list"})
     */
    private ?array $defaultFieldPropertiesMaterial;
    
    /**
     * @ORM\Column(type="json", nullable=true)
     * @Groups({"detail", "list"})
     */
    private ?array $defaultFieldPropertiesTool;
    
    /**
     * @ORM\Column(type="json", nullable=true)
     * @Groups({"detail", "list"})
     */
    private ?array $defaultFieldPropertiesKeyy;
    
    /**
     * @ORM\OneToMany(targetEntity="PdfDocumentType", mappedBy="pdfSpecification")
     */
    private Collection $pdfItemTypes;
    
    /**
     * @ORM\Embedded(class="App\Entity\Data\PdfSpecificationType", columnPrefix="pdfSpecificationType_")
     */
    private PdfSpecificationType $pdfSpecificationType;
    
    public function __construct(
        string $id,
        string $name,
        float $labelWidth,
        float $labelHeight,
        bool $printHeader,
        bool $printFooter,
        float $labelStartX,
        float $labelStartY,
        int $labelsPerColumn,
        int $rowsPerPage,
        int $copies,
        array $itemFieldDimensions,
        array $commonFieldDimensions,
        array $defaultCommonFieldTyes,
        array $defaultCommonFieldParams,
        array $defaultCommonFieldProperties,
        array $defaultFieldTypesMaterial,
        array $defaultFieldPropertiesMaterial,
        array $defaultFieldTypesTool,
        array $defaultFieldPropertiesTool,
        array $defaultFieldTypesKeyy,
        array $defaultFieldPropertiesKeyy,
        int $itemFieldsCount,
        int $commonFieldsCount,
        PdfSpecificationType $pdfSpecificationType,
        $pageSize = 'A4',
        ?string $orientation = 'P',
        ?string $backgroundImage = null,
        ?bool $autoIncreaseFontSize = false
    )
    {
        $this->id = $id;
        $this->createdAt = new DateTimeImmutable();
        $this->name = $name;
        $this->pdfSpecificationType = $pdfSpecificationType;
        $this->pageSize = json_encode($pageSize);
        $this->orientation = $orientation ?? 'P';
        $this->labelWidth = $labelWidth;
        $this->labelHeight = $labelHeight;
        $this->printHeader = $printHeader;
        $this->printFooter = $printFooter;
        $this->labelStartX = $labelStartX;
        $this->labelStartY = $labelStartY;
        $this->itemFieldsCount = $itemFieldsCount;
        $this->commonFieldsCount = $commonFieldsCount;
        $this->copies = $copies;
        $this->autoIncreaseFontSize = $autoIncreaseFontSize ?? false;
        
        $itemFieldD = [];
        /** @var PdfFieldDimensions $itemFieldDimension */
        foreach ($itemFieldDimensions as $itemFieldDimension) {
            $itemFieldD[] = $itemFieldDimension->toArray();
        }
        $this->itemFieldDimensions = $itemFieldD;
        
        $commonFieldD = [];
        /** @var PdfFieldDimensions $commonFieldDimension */
        foreach ($commonFieldDimensions as $commonFieldDimension) {
            $commonFieldD[] = $commonFieldDimension->toArray();
        }
        $this->commonFieldDimensions = $commonFieldD;
        
        $this->defaultCommonFieldProperties = $defaultCommonFieldProperties;
        $this->defaultCommonFieldTypes = $defaultCommonFieldTyes;
        $this->defaultCommonFieldParams = $defaultCommonFieldParams;
        $this->defaultFieldPropertiesMaterial = $defaultFieldPropertiesMaterial;
        $this->defaultFieldTypesMaterial = $defaultFieldTypesMaterial;
        $this->defaultFieldTypesTool = $defaultFieldTypesTool;
        $this->defaultFieldPropertiesTool = $defaultFieldPropertiesTool;
        $this->defaultFieldTypesKeyy = $defaultFieldTypesKeyy;
        $this->defaultFieldPropertiesKeyy = $defaultFieldPropertiesKeyy;
        $this->labelsPerColumn = $labelsPerColumn;
        $this->rowsPerPage = $rowsPerPage;
        $this->pdfItemTypes = new ArrayCollection();
        $this->backgroundImage = $backgroundImage;
    }
    
    public function getId(): string
    {
        return $this->id;
    }
    
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
    
    public function getPageSize()
    {
        return json_decode($this->pageSize);
    }
    
    public function getOrientation(): string
    {
        return $this->orientation;
    }
    
    public function getLabelWidth(): float
    {
        return $this->labelWidth;
    }
    
    public function getLabelHeight(): float
    {
        return $this->labelHeight;
    }
    
    public function isPrintHeader(): bool
    {
        return $this->printHeader;
    }
    
    public function isPrintFooter(): bool
    {
        return $this->printFooter;
    }
    
    public function getLabelStartX(): float
    {
        return $this->labelStartX;
    }
    
    public function getLabelStartY(): float
    {
        return $this->labelStartY;
    }
    
    public function getLabelsPerColumn(): int
    {
        return $this->labelsPerColumn;
    }
    
    public function getRowsPerPage(): int
    {
        return $this->rowsPerPage;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    /**
     * @return PdfFieldDimensions[]
     */
    public function getItemFieldDimensionDtos(): array
    {
        $itemFieldDimensions = [];
        foreach ($this->itemFieldDimensions as $itemFieldDimension) {
            $itemFieldDimensions[] = PdfFieldDimensions::fromArray($itemFieldDimension);
        }
        return $itemFieldDimensions;
    }
    
    /**
     * @return PdfFieldDimensions[]
     */
    public function getCommonFieldDimensionDtos(): array
    {
        $commonFieldDimensions = [];
        foreach ($this->commonFieldDimensions as $commonFieldDimension) {
            $commonFieldDimensions[] = PdfFieldDimensions::fromArray($commonFieldDimension);
        }
        return $commonFieldDimensions;
    }
    
    public function getDefaultCommonFieldTypes(): array
    {
        return $this->defaultCommonFieldTypes ?? [];
    }
    
    public function getDefaultCommonFieldParams(): array
    {
        return $this->defaultCommonFieldParams ?? [];
    }
    
    public function getDefaultFieldTypesMaterial(): array
    {
        return $this->defaultFieldTypesMaterial ?? [];
    }
    
    public function getItemFieldDimensions(): array
    {
        return $this->itemFieldDimensions;
    }
    
    public function getCommonFieldDimensions(): array
    {
        return $this->commonFieldDimensions;
    }
    
    public function getDefaultFieldPropertiesMaterial(): array
    {
        return $this->defaultFieldPropertiesMaterial ?? [];
    }
    
    public function getDefaultCommonFieldProperties(): array
    {
        return $this->defaultCommonFieldProperties ?? [];
    }
    
    public function getItemFieldsCount(): int
    {
        return $this->itemFieldsCount;
    }
    
    public function getCommonFieldsCount(): int
    {
        return $this->commonFieldsCount;
    }
    
    public function getDefaultFieldPropertiesTool(): array
    {
        return $this->defaultFieldPropertiesTool ?? [];
    }
    
    
    public function getDefaultFieldTypesTool(): array
    {
        return $this->defaultFieldTypesTool ?? [];
    }
    
    public function getDefaultFieldTypesKeyy(): array
    {
        return $this->defaultFieldTypesKeyy ?? [];
    }
    
    public function getDefaultFieldPropertiesKeyy(): array
    {
        return $this->defaultFieldPropertiesKeyy ?? [];
    }
    
    /**
     * @return PdfField[]
     * @Groups({"detail", "list"})
     */
    public function getDefaultCommonPdfFields(): array
    {
        $pdfFields = [];
        foreach ($this->defaultCommonFieldProperties as $index => $defaultFieldProperty) {
            $pdfFields[] = [
                "property" => $defaultFieldProperty,
                "type" => $this->defaultCommonFieldTypes[$index],
                "position" => $index,
                "params" => $this->defaultCommonFieldParams[$index],
            ];
        }
        return $pdfFields;
    }
    
    /**
     * @return PdfField[]
     * @Groups({"detail", "list"})
     */
    public function getDefaultPdfFieldsMaterial(): array
    {
        $pdfFields = [];
        foreach ($this->defaultFieldPropertiesMaterial as $index => $defaultFieldProperty) {
            $pdfFields[] = [
                "property" => $defaultFieldProperty,
                "type" => $this->defaultFieldTypesMaterial[$index],
                "position" => $index,
                "params" => ""
            ];
        }
        return $pdfFields;
    }
    
    /**
     * @return PdfField[]
     * @Groups({"detail", "list"})
     */
    public function getDefaultPdfFieldsTool(): array
    {
        $pdfFields = [];
        foreach ($this->defaultFieldPropertiesTool as $index => $defaultFieldProperty) {
            $pdfFields[] = [
                "property" => $defaultFieldProperty,
                "type" => $this->defaultFieldTypesTool[$index],
                "position" => $index,
                "params" => ""
            ];
        }
        return $pdfFields;
    }
    
    /**
     * @return PdfField[]
     * @Groups({"detail", "list"})
     */
    public function getDefaultPdfFieldsKeyy(): array
    {
        $pdfFields = [];
        foreach ($this->defaultFieldPropertiesKeyy as $index => $defaultFieldProperty) {
            $pdfFields[] = [
                "property" => $defaultFieldProperty,
                "type" => $this->defaultFieldTypesKeyy[$index],
                "position" => $index,
                "params" => ""
            ];
        }
        return $pdfFields;
    }
    
    public function getPdfSpecificationType(): PdfSpecificationType
    {
        return $this->pdfSpecificationType;
    }
    
    /**
     * @Groups({"detail", "list"})
     */
    public function getPdfSpecifictaionTypeValue(): string
    {
        return $this->pdfSpecificationType->getValue();
    }
    
    public function getCopies(): int
    {
        return $this->copies;
    }
    
    public function getBackgroundImage(): ?string
    {
        return $this->backgroundImage;
    }
    
    public function isAutoIncreaseFontSize(): bool
    {
        return $this->autoIncreaseFontSize;
    }
}
