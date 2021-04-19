<?php


namespace App\Entity;

use App\Entity\Data\File;
use App\Event\Log;
use App\Exceptions\Domain\InvalidArgumentException;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * @ORM\Entity(repositoryClass="App\Repository\KeyyRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="idx_itemNumber", columns={"company_id", "item_number"})})
 */
class Keyy implements CompanyAwareInterface, FileAwareInterface, LoggableInterface, PermissionAwareInterface, DeleteUpdateAwareInterface
{
    const PERMISSION = 'keyy';
    
    use TaskTrait;
    
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=255)
     * @Groups({"detail", "list"})
     */
    private string $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="keyys")
     */
    private Company $company;
    
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
     */
    private bool $deleted;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Log()
     * @Groups({"detail"})
    */
    private ?int $amount = null;
    
    /**
     * @ORM\Column(type="boolean")
     * @Log()
     */
    private bool $isArchived;
    
    /**
     * @ORM\Column(type="string", length=50)
     * @Log()
     * @Groups({"list", "detail"})
     */
    private string $itemNumber;
    
    /**
     * @ORM\Column(type="string", length=255)
     * @Log()
     * @Groups({"list", "detail"})
     */
    private string $name;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Log()
     * @Groups({"detail"})
     */
    private ?string $address;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     * @Log()
     * @Groups({"detail"})
     */
    private ?string $note;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Location", inversedBy="keyyHomes")
     * @Log()
     * @Groups({"list", "detail"})
     */
    private Location $home;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Location", inversedBy="keyyOwners")
     * @Log()
     * @Groups({"list", "detail"})
     */
    private Location $owner;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PermissionGroup", inversedBy="keyys")
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
     * @Groups({"detail"})
     * @Log()
     */
    private ?array $customFields = null;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Task", fetch="EAGER", mappedBy="keyy")
     */
    private Collection $tasks;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ConsignmentItem", mappedBy="keyy")
     */
    private Collection $consignmentItems;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\OwnerChange", mappedBy="keyy")
     */
    private Collection $ownerChanges;
    
    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private ?array $altScannerIds = null;
    
    public function __construct(string $itemNumber, string $name, Location $home, Location $owner, Company $company)
    {
        $this->company = $company;
        $this->id = Uuid::uuid4()->toString();
        $this->createdAt = new DateTimeImmutable();
        $this->itemNumber = $itemNumber;
        $this->isArchived = false;
        $this->home = $home;
        $this->owner = $owner;
        $this->name = $name;
        $this->files = [];
        $this->tasks = new ArrayCollection();
        $this->consignmentItems = new ArrayCollection();
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

    public function getCompany(): Company
    {
        return $this->company;
    }
    
    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function getitemNumber(): string
    {
        return $this->itemNumber;
    }

    public function getName(): string
    {
        return $this->name;
    }
    
    public function getAddress(): ?string
    {
        return $this->address;
    }
    
    public function getNote(): ?string
    {
        return $this->note;
    }
    
    public function getHome(): string
    {
        return $this->home->getName() ?? '';
    }
    
    public function getOwner(): string
    {
        return $this->owner->getName() ?? '';
    }
    
    public function getPermissionGroup(): ?string
    {
        return $this->permissionGroup ? $this->permissionGroup->getName() : null;
    }
    
    /**
     * @Groups({"detail"})
     */
    public function getPermissionGroupId(): ?string
    {
        return $this->permissionGroup ? $this->permissionGroup->getId() : null;
    }
    
    public function assignName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function annotate(?string $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function setAmount(?int $amount): self
    {
        if ($amount < 0) {
            throw InvalidArgumentException::forInvalidRange($amount, '>= 0');
        }
        
        $this->amount = $amount;

        return $this;
    }
    
    public function setitemNumber(string $itemNumber): self
    {
        $this->itemNumber = $itemNumber;
    
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
    
    public function setPermissionGroup(?PermissionGroup $permissionGroup): void
    {
        $this->permissionGroup = $permissionGroup;
    }
    
    public function setIsArchived(bool $isArchived): void
    {
        $this->isArchived = $isArchived;
    }
    
    public function getIsArchived(): bool
    {
        return $this->isArchived;
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
        return 'keyy';
    }
    
    public function isDeleted(): bool
    {
        return $this->deleted;
    }
    
    public function setDeleted(bool $deleted): void
    {
        $this->deleted = $deleted;
    }
    
    public function setUpdatedAt(DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
    
    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }
    
}
