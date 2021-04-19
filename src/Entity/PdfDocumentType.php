<?php

declare(strict_types=1);


namespace App\Entity;


use App\Entity\Data\EntityType;
use App\Entity\Data\PdfField;
use App\Entity\Data\PdfSpecificationType;
use App\Exceptions\Domain\InconsistentDataException;
use App\Services\Pdf\PdfSpecification;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PdfDocumentTypeRepository")
 */
class PdfDocumentType implements CompanyAwareInterface
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=255)
     * @Groups({"detail", "list"})
     */
    private string $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="pdfDocumentTypes")
     */
    private Company $company;
    
    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private DateTimeImmutable $createdAt;
    
    /**
     * @ORM\Column(type="json")
     * @Groups({"detail", "list"})
     */
    private array $itemFields;
    
    /**
     * @ORM\Column(type="json")
     * @Groups({"detail", "list"})
     */
    private array $commonFields;
    
    /**
     * @ORM\Column(type="string")
     * @Groups({"detail", "list"})
     */
    private string $name;
    
    /**
     * @ORM\Column(type="string")
     */
    private string $pdfSpecificationId;
    
    /**
     * @ORM\Embedded(class="App\Entity\Data\EntityType", columnPrefix="entityType_")
     * @Groups({"list", "detail"})
     */
    private EntityType $entityType;
    
    /**
     * @ORM\Embedded(class="App\Entity\Data\PdfSpecificationType", columnPrefix="pdfSpecificationType_")
     * @Groups({"list", "detail"})
     */
    private PdfSpecificationType $pdfSpecificationType;
    
    /**
     * @Groups({"list", "detail"})
     */
    private ?PdfSpecification $pdfSpecification = null;
    
    public function __construct(
        Company $company,
        string $name,
        EntityType $entityType,
        PdfSpecification $pdfSpecification,
        ?array $itemFields = null,
        ?array $commonFields = null
    )
    {
        $this->company = $company;
        $this->id = Uuid::uuid4()->toString();
        $this->createdAt = new DateTimeImmutable();
        $this->name = $name;
        $this->pdfSpecificationId = $pdfSpecification->getId();
        $this->pdfSpecificationType = $pdfSpecification->getPdfSpecificationType();
        $this->entityType = $entityType;
        $this->selectedMaterialLabelTypes = new ArrayCollection();
        $this->selectedToolLabelTypes = new ArrayCollection();
        $this->selectedKeyyLabelTypes = new ArrayCollection();
        $this->itemFields = [];
        
        if (!$itemFields) {
            switch ($entityType->getValue()) {
                case EntityType::material()->getValue():
                case EntityType::order()->getValue():
                case EntityType::consignment()->getValue():
                    $itemFields = $pdfSpecification->getDefaultPdfFieldsMaterial();
                    break;
                case EntityType::tool()->getValue():
                    $itemFields = $pdfSpecification->getDefaultPdfFieldsTool();
                    break;
                case EntityType::keyy()->getValue():
                    $itemFields = $pdfSpecification->getDefaultPdfFieldsKeyy();
                    break;
            }
        }
        
        if ($itemFields && count($itemFields) !== $pdfSpecification->getItemFieldsCount()) {
            throw InconsistentDataException::forPdfFieldCountDoesNotMatchSpecification(count($itemFields), $pdfSpecification->getItemFieldsCount(), "itemFields", $pdfSpecification->getName());
        }
        foreach ($itemFields as $itemField) {
            $this->addItemField(PdfField::fromArray($itemField));
        }
        
        $this->commonFields = [];
    
        if (!$commonFields) {
            $commonFields = $pdfSpecification->getDefaultCommonPdfFields();
        }
        
        if (count($commonFields) !== $pdfSpecification->getCommonFieldsCount()) {
            throw InconsistentDataException::forPdfFieldCountDoesNotMatchSpecification(count($commonFields), $pdfSpecification->getCommonFieldsCount(), "commonFields", $pdfSpecification->getName());
        }
        foreach ($commonFields as $commonField) {
            $this->addCommonField(PdfField::fromArray($commonField));
        }
    }
    
    public function getId(): string
    {
        return $this->id;
    }
    
    public function getCompany(): Company
    {
        return $this->company;
    }
    
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getEntityType(): string
    {
        return $this->entityType->getValue();
    }
    
    public function setName(string $name): void
    {
        $this->name = $name;
    }
    
    public function getItemFields(): array
    {
        return array_values($this->itemFields);
    }
    
    /**
     * @return PdfField[]
     */
    public function getItemFieldsAsItemFields(): array
    {
        $itemFields = [];
        foreach ($this->itemFields as $itemField ) {
            $itemFields[] = PdfField::fromArray($itemField);
        }
        return array_values($itemFields);
    }
    
    public function addItemField(PdfField $itemField): void
    {
        $this->itemFields[] = $itemField->toArray();
    }
    
    public function updateItemField(PdfField $itemFieldToUpdate): void
    {
        /** @var PdfField $itemField */
        foreach ($this->itemFields as $index => $pdfItemArray) {
            $itemField = PdfField::fromArray($pdfItemArray);
            if ($itemField->getPosition() === $itemFieldToUpdate->getPosition()) {
                $this->itemFields[$index] = $itemFieldToUpdate->toArray();
            }
        }
    }
    
    public function removeItemField(PdfField $itemFieldToRemove): void
    {
        foreach ($this->itemFields as $index => $itemFieldArray) {
            $itemField = PdfField::fromArray($itemFieldArray);
            if ($itemField->getPosition() === $itemFieldToRemove->getPosition()) {
                unset($this->itemFields[$index]);
            }
        }
        $this->itemFields = array_values($this->itemFields);
    }
    
    public function removeAllItemFields(): void
    {
        foreach ($this->itemFields as $itemField) {
            $this->removeItemField(PdfField::fromArray($itemField));
        }
    }
    
    public function getCommonFields(): array
    {
        return array_values($this->commonFields);
    }
    
    /**
     * @return PdfField[]
     */
    public function getCommonFieldsAsCommonFields(): array
    {
        $commonFields = [];
        foreach ($this->commonFields as $commonField ) {
            $commonFields[] = PdfField::fromArray($commonField);
        }
        return array_values($commonFields);
    }
    
    public function addCommonField(PdfField $commonField): void
    {
        $this->commonFields[] = $commonField->toArray();
    }
    
    public function updateCommonField(PdfField $commonFieldToUpdate): void
    {
        /** @var PdfField $commonField */
        foreach ($this->commonFields as $index => $pdfCommonArray) {
            $commonField = PdfField::fromArray($pdfCommonArray);
            if ($commonField->getPosition() === $commonFieldToUpdate->getPosition()) {
                $this->commonFields[$index] = $commonFieldToUpdate->toArray();
            }
        }
    }
    
    public function removeCommonField(PdfField $commonFieldToRemove): void
    {
        foreach ($this->commonFields as $index => $commonFieldArray) {
            $commonField = PdfField::fromArray($commonFieldArray);
            if ($commonField->getPosition() === $commonFieldToRemove->getPosition()) {
                unset($this->commonFields[$index]);
            }
        }
        $this->commonFields = array_values($this->commonFields);
    }
    
    public function removeAllCommonFields(): void
    {
        foreach ($this->commonFields as $commonField) {
            $this->removeCommonField(PdfField::fromArray($commonField));
        }
    }
    
    public function setPdfSpecification(PdfSpecification $pdfSpecification): void
    {
        $this->pdfSpecification = $pdfSpecification;
    }
    
    public function getPdfSpecification(): ?PdfSpecification
    {
        return $this->pdfSpecification;
    }
    
    public function getPdfSpecificationId(): string
    {
        return $this->pdfSpecificationId;
    }
    
    public function getPdfSpecificationType(): string
    {
        return $this->pdfSpecificationType->getValue();
    }
    
}
