<?php


namespace App\Entity;

use App\Entity\Data\File;
use App\Event\Log;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * @ORM\Entity(repositoryClass="App\Repository\ToolRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="idx_itemNumber", columns={"company_id", "item_number"})})
 */
class Tool implements CompanyAwareInterface, FileAwareInterface, LoggableInterface, PermissionAwareInterface, DeleteUpdateAwareInterface
{
    const PERMISSION = 'tool';
    
    use TaskTrait;
    
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=255)
     * @Groups({"detail", "list"})
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
     * @ORM\Column(type="boolean")
     * @Log()
     */
    private bool $isArchived;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private bool $deleted;
    
    /**
     * @ORM\Column(type="string", length=255)
     * @Log()
     * @Groups({"list", "detail"})
     */
    private string $name;
    
    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Log()
     * @Groups({"list", "detail"})
     */
    private ?DateTimeImmutable $usableTill = null;
    
    /**
     * @ORM\Column(type="float", nullable=true)
     * @Log()
     * @Groups({"list", "detail"})
     */
    private ?float $purchasingPrice = null;

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     * @Groups({"list", "detail"})
     */
    private ?DateTimeImmutable $purchasingDate;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     * @Log()
     * @Groups({"list", "detail"})
     */
    private ?string $note = null;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="tools")
     */
    private Company $company;
    
    /**
     * @ORM\Column(type="boolean")
     * @Log()
     * @Groups({"list", "detail"})
     */
    private bool $isBroken;
    
    /**
     * @ORM\Column(type="string", length=50)
     * @Log()
     * @Groups({"list", "detail"})
     */
    private string $itemNumber;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Log()
     * @Groups({"list", "detail"})
     */
    private ?string $manufacturerNumber = null;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Log()
     * @Groups({"list", "detail"})
     */
    private ?string $manufacturerName = null;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Log()
     * @Groups({"list", "detail"})
     */
    private ?string $barcode = null;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Log()
     * @Groups({"list", "detail"})
     */
    private ?string $blecode = null;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Location", inversedBy="toolHomes")
     * @Log()
     * @Groups({"list", "detail"})
     */
    private Location $home;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Location", inversedBy="toolOwners")
     * @Log()
     * @Groups({"list", "detail"})
     */
    private Location $owner;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ItemGroup", inversedBy="tools")
     * @Log()
     * @Groups({"detail", "list"})
     */
    private ?ItemGroup $itemGroup = null;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PermissionGroup", inversedBy="tools")
     * @Log()
     * @Groups({"list", "detail"})
     */
    private ?PermissionGroup $permissionGroup = null;
    
    /**
     * @ORM\Column(type="json")
     * @Groups({"detail"})
     */
    private array $files;
    
    /**
     * @ORM\Column(type="json", nullable=true)
     * @Groups({"list", "detail"})
     * @Log()
     */
    private ?array $customFields = null;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Task", fetch="EAGER", mappedBy="tool")
     */
    private Collection $tasks;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ConsignmentItem", mappedBy="tool")
     */
    private Collection $consignmentItems;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\OwnerChange", mappedBy="tool")
     */
    private Collection $ownerChanges;
    
    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private ?array $altScannerIds = null;
    
    public function __construct(string $itemNumber, string $name, Location $home, Location $owner, Company $company)
    {
        $this->company = $company;
        $this->itemNumber = $itemNumber;
        $this->id = Uuid::uuid4()->toString();
        $this->createdAt = new DateTimeImmutable();
        $this->isArchived = false;
        $this->name = $name;
        $this->home = $home;
        $this->owner = $owner;
        $this->isBroken = false;
        $this->files = [];
        $this->tasks = new ArrayCollection();
        $this->consignmentItems = new ArrayCollection();
        $this->ownerChanges = new ArrayCollection();
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
    
    public function getUsableTill(): ?string
    {
        return $this->usableTill ? $this->usableTill->format('Y-m-d') : null;
    }

    public function getPurchasingPrice(): ?float
    {
        return $this->purchasingPrice;
    }
    
    public function getPurchasingDate(): ?string
    {
        if ($this->purchasingDate) {
            return $this->purchasingDate->format('Y-m-d');
        }
        return null;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }
    
    public function isBroken(): bool
    {
        return $this->isBroken;
    }

    public function getitemNumber(): string
    {
        return $this->itemNumber;
    }

    public function getManufacturerNumber(): ?string
    {
        return $this->manufacturerNumber;
    }

    public function getManufacturerName(): ?string
    {
        return $this->manufacturerName;
    }

    public function getBarcode(): ?string
    {
        return $this->barcode;
    }

    public function getBlecode(): ?string
    {
        return $this->blecode;
    }
    
    public function getHome(): string
    {
        return $this->home->getName() ?? '';
    }

    public function getOwner(): string
    {
        return $this->owner->getName() ?? '';
    }
    
    public function getCompany(): Company
    {
        return $this->company;
    }

    public function getItemGroup(): ?ItemGroup
    {
        return $this->itemGroup;
    }
    
    public function setName(string $name): self
    {
        $this->name = $name;
        
        return $this;
    }
    
    public function setUsableTill(?DateTimeImmutable $usableTill): void
    {
        $this->usableTill = $usableTill;
    }
    
    public function setPurchasingPrice(?float $purchasingPrice): self
    {
        $this->purchasingPrice = $purchasingPrice;
        
        return $this;
    }

    public function setPurchasingDate(?DateTimeImmutable $purchasingDate): self
    {
        $this->purchasingDate = $purchasingDate;

        return $this;
    }
    
    public function setNote(?string $note): self
    {
        $this->note = $note;
        
        return $this;
    }
    
    public function setIsBroken(bool $isBroken): self
    {
        $this->isBroken = $isBroken;
        
        return $this;
    }
    
    public function setItemNumber(string $itemNumber): self
    {
        $this->itemNumber = $itemNumber;
        
        return $this;
    }
    
    public function setManufacturerNumber(?string $manufacturerNumber): self
    {
        $this->manufacturerNumber = $manufacturerNumber;
        
        return $this;
    }
    
    public function setManufacturerName(?string $manufacturerName): self
    {
        $this->manufacturerName = $manufacturerName;
        
        return $this;
    }
    
    public function setBarcode(?string $barcode): self
    {
        $this->barcode = $barcode;
        
        return $this;
    }
    
    public function setBlecode(?string $blecode): self
    {
        $this->blecode = $blecode;
        
        return $this;
    }
    
    public function setHome(Location $home): self
    {
        $this->home = $home;
        
        return $this;
    }
    
    public function setOwner(Location $owner): self
    {
        $this->owner = $owner;
        
        return $this;
    }
    
    public function setItemGroup(?ItemGroup $itemGroup): self
    {
        $this->itemGroup = $itemGroup;
        
        return $this;
    }
    
    public function getIsArchived(): bool
    {
        return $this->isArchived;
    }
    
    public function setIsArchived(bool $isArchived): void
    {
        $this->isArchived = $isArchived;
    }
    
    public function getPermissionGroup(): ?string
    {
        return $this->permissionGroup ? $this->permissionGroup->getName() : null;
    }
    
    /**
     * @Groups({"list", "detail"})
     */
    public function getPermissionGroupId(): ?string
    {
        return $this->permissionGroup ? $this->permissionGroup->getId() : null;
    }
    
    public function setPermissionGroup(?PermissionGroup $permissionGroup): void
    {
        $this->permissionGroup = $permissionGroup;
    }
    
    public function getFiles(): array
    {
        $files = [];
        foreach ( $this->files as $file ) {
            if ( $file['docType'] !== 'thumb' ) {
                $files[] = $file;
            }
        }
        return $files;
    }
    
    public function addFile(File $fileToAdd): void
    {
        $this->files[] = $fileToAdd->toArray();
    }
    
    public function updateFile(File $fileToUpdate): void
    {
        /** @var File $file */
        foreach ($this->files as $index => $fileArray) {
            $file = File::fromArray($fileArray);
            if ($file->getRelativePath() === $fileToUpdate->getRelativePath()) {
                $this->files[$index] = $fileToUpdate->toArray();
            }
        }
    }
    
    public function removeFile(File $fileToRemove): void
    {
        foreach ($this->files as $index => $fileArray) {
            $file = File::fromArray($fileArray);
            if ($file->getRelativePath() === $fileToRemove->getRelativePath()) {
                unset($this->files[$index]);
            }
        }
        $this->files = array_values($this->files);
    }
    
    /**
     * @Groups({"list", "detail"})
     */
    public function getThumb(): ?string
    {
        /** @var File $file */
        foreach ($this->files as $index => $fileArray) {
            $file = File::fromArray($fileArray);
            if ($file->getDocType() === 'thumb') {
                return $file->getRelativePath();
            }
        }
        return null;
    }
    
    /**
     * @Groups({"list", "detail"})
     */
    public function getThumbFile(): ?File
    {
        /** @var File $file */
        foreach ($this->files as $index => $fileArray) {
            $file = File::fromArray($fileArray);
            if ($file->getDocType() === 'thumb') {
                return $file;
            }
        }
        return null;
    }
    
    /**
     * @Groups({"list", "detail"})
     */
    public function getProfileImage(): ?string
    {
        /** @var File $file */
        foreach ($this->files as $index => $fileArray) {
            $file = File::fromArray($fileArray);
            if ($file->getDocType() === 'profileImage') {
                return $file->getRelativePath();
            }
        }
        return null;
    }
    
    public function getAllFiles(): array
    {
        return $this->files;
    }
    
    public function getLogData(): string
    {
        return $this->getitemNumber();
    }
    
    public function getCustomFields(): ?array
    {
        return $this->customFields;
    }
    
    public function setCustomFields(?array $customFields): void
    {
        $this->customFields = $customFields;
    }
    
    public function getConsignmentItems()
    {
        return $this->consignmentItems;
    }
    
    public function getOwnerChanges()
    {
        return $this->ownerChanges;
    }
    
    public function getPermissionType(): string
    {
        return self::PERMISSION;
    }
    
    public function setAltScannerIds(?array $altScannerIds): void
    {
        $this->altScannerIds = $altScannerIds;
    }
    
    public function getAltScannerIds(): ?array
    {
        return $this->altScannerIds;
    }
    
    /**
     * @Groups({"scannerResult"})
     */
    public function getType(): string
    {
        return 'tool';
    }
    
    public function isDeleted(): bool
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
