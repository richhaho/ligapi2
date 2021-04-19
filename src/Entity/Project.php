<?php

declare(strict_types=1);


namespace App\Entity;


use App\Event\Log;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProjectRepository")
 */
class Project implements LoggableInterface, CompanyAwareInterface, PermissionAwareInterface, DeleteUpdateAwareInterface
{
    const PERMISSION = 'project';
    
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=255)
     * @Groups({"list", "detail"})
     */
    private string $id;
    
    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"detail", "list"})
     */
    private DateTimeImmutable $createdAt;
    
    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"detail", "list"})
     */
    private ?DateTimeImmutable $updatedAt = null;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="projects")
     */
    private Company $company;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private bool $deleted;
    
    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"list", "detail", "stocktaking", "listStockChanges"})
     * @Log()
     */
    private string $name;
    
    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"list", "detail", "stocktaking", "listStockChanges"})
     * @Log()
     */
    private ?DateTimeImmutable $projectDate = null;
    
    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"list", "detail", "stocktaking", "listStockChanges"})
     * @Log()
     */
    private ?DateTimeImmutable $projectEnd = null;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Customer", inversedBy="projects")
     * @Groups({"list", "detail", "listStockChanges"})
     */
    private ?Customer $customer = null;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Consignment", mappedBy="project")
     */
    private Collection $consignments;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MaterialLocation", mappedBy="project")
     */
    private Collection $materialLocations;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\StockChange", mappedBy="project")
     */
    private Collection $stockChanges;
    
    public function __construct(string $name, Company $company)
    {
        $this->id = Uuid::uuid4()->toString();
        $this->createdAt = new DateTimeImmutable();
        $this->company = $company;
        $this->name = $name;
        $this->consignments = new ArrayCollection();
        $this->stockChanges = new ArrayCollection();
        $this->materialLocations = new ArrayCollection();
        $this->deleted = false;
    }
    
    public function getId(): string
    {
        return $this->id;
    }
    
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
    
    public function getName(): string
    {
        return $this->name;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }
    
    public function setName(string $name): self
    {
        $this->name = $name;
        
        return $this;
    }
    
    public function setCustomer(?Customer $customer): self
    {
        $this->customer = $customer;
        
        return $this;
    }
    
    public function getLogData(): string
    {
        return $this->getName();
    }
    
    public function getConsignments()
    {
        return $this->consignments;
    }
    
    public function getStockChanges()
    {
        return $this->stockChanges;
    }
    
    public function getProjectDate(): ?string
    {
        if (!$this->projectDate) {
            return null;
        }
        return $this->projectDate->format('Y-m-d');
    }
    
    public function getProjectDateDateTimeImmutable(): ?DateTimeImmutable
    {
        return $this->projectDate;
    }
    
    public function setProjectDate(?DateTimeImmutable $projectDate): void
    {
        $this->projectDate = $projectDate;
    }
    
    public function getProjectEnd(): ?string
    {
        if (!$this->projectEnd) {
            return null;
        }
        return $this->projectEnd->format('Y-m-d');
    }
    
    public function getProjectEndDateDateTimeImmutable(): ?DateTimeImmutable
    {
        return $this->projectEnd;
    }
    
    public function setProjectEnd(?DateTimeImmutable $projectEnd): void
    {
        $this->projectEnd = $projectEnd;
    }
    
    public function getCompany(): Company
    {
        return $this->company;
    }
    
    public function getPermissionType(): string
    {
        return self::PERMISSION;
    }
    
    public function getDeleted(): bool
    {
        return $this->deleted;
    }
    
    public function setDeleted(bool $deleted): void
    {
        $this->deleted = $deleted;
    }
    
    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }
    
    public function setUpdatedAt(DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
    
}
