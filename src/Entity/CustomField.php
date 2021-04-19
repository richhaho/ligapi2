<?php

declare(strict_types=1);


namespace App\Entity;


use App\Entity\Data\CustomFieldType;
use App\Entity\Data\EntityType;
use App\Event\Log;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CustomFieldRepository")
 */
class CustomField implements LoggableInterface, CompanyAwareInterface
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=255)
     * @Groups({"list", "detail"})
     */
    private string $id;
    
    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private DateTimeImmutable $createdAt;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="customFields")
     */
    private Company $company;
    
    /**
     * @ORM\Column(type="string")
     * @Groups({"list", "detail"})
     * @Log()
     */
    private string $name;
    
    /**
     * @ORM\Column(type="json", nullable=true)
     * @Groups({"list", "detail"})
     * @Log()
     */
    private ?array $options = null;
    
    /**
     * @ORM\Embedded(class="App\Entity\Data\CustomFieldType", columnPrefix="customFieldType_")
     * @Groups({"list", "detail"})
     * @Log()
     */
    private CustomFieldType $type;
    
    /**
     * @ORM\Embedded(class="App\Entity\Data\EntityType", columnPrefix="entityType_")
     * @Groups({"list", "detail"})
     * @Log()
     */
    private EntityType $entityType;
    
    public function __construct(Company $company, string $name, CustomFieldType $type, EntityType $entityType)
    {
        $this->id = Uuid::uuid4()->toString();
        $this->createdAt = new DateTimeImmutable();
        $this->company = $company;
        $this->name = $name;
        $this->type = $type;
        $this->entityType = $entityType;
    }
    
    public function getId(): string
    {
        return $this->id;
    }
    
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
    
    public function getCompany(): Company
    {
        return $this->company;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getOptions(): ?array
    {
        return $this->options;
    }
    
    public function getType(): string
    {
        return $this->type->getValue();
    }
    
    public function setName(string $name): void
    {
        $this->name = $name;
    }
    
    public function setOptions(?array $options): void
    {
        $this->options = $options;
    }
    
    
    public function getLogData(): string
    {
        return $this->name;
    }
    
    public function getEntityType(): string
    {
        return $this->entityType->getValue();
    }
}
