<?php

declare(strict_types=1);


namespace App\Entity;


use App\Entity\Data\File;
use App\Event\Log;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StockChangeRepository")
 */
class StockChange implements FileAwareInterface, CompanyAwareInterface
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=255)
     * @Groups({"detail", "list", "stocktaking", "listStockChanges"})
     */
    private string $id;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $originalId = null;
    
    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"detail", "listStockChanges", "stocktaking"})
     */
    private DateTimeImmutable $createdAt;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="stockChanges")
     */
    private Company $company;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="stockChanges")
     * @Groups({"detail", "listStockChanges"})
     * @Log()
     */
    private User $user;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"detail", "listStockChanges"})
     * @Log()
     */
    private ?string $note;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MaterialLocation", inversedBy="stockChanges")
     * @Groups({"detail", "listStockChanges"})
     */
    private MaterialLocation $materialLocation;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Project", inversedBy="stockChanges")
     * @Groups({"detail", "listStockChanges"})
     */
    private ?Project $project;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"detail", "list", "listStockChanges"})
     * @Log()
     */
    private ?float $amount;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"detail", "list", "listStockChanges"})
     * @Log()
     */
    private ?float $amountAlt = null;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"detail", "list", "listStockChanges"})
     */
    private ?float $newCurrentStock;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"detail", "list", "listStockChanges"})
     */
    private ?float $newCurrentStockAlt = null;
    
    /**
     * @ORM\Column(type="json", nullable=true)
     * @Groups({"detail", "list", "listStockChanges"})
     */
    private ?array $document = null;
    
    public function __construct(
        Company $company,
        User $user,
        ?string $note,
        MaterialLocation $materialLocation,
        ?float $amount,
        ?float $amountAlt,
        ?float $newCurrentStock,
        ?float $newCurrentStockAlt,
        ?Project $project,
        ?string $originalId = null,
        ?string $createdAt = null
    )
    {
        $this->id = Uuid::uuid4()->toString();
        $this->company = $company;
        if ($createdAt) {
            $this->createdAt = new DateTimeImmutable($createdAt);
        } else {
            $this->createdAt = new DateTimeImmutable();
        }
        $this->company = $company;
        $this->note = $note;
        $this->materialLocation = $materialLocation;
        $this->amount = $amount;
        $this->amountAlt = $amountAlt;
        $this->newCurrentStock = $newCurrentStock;
        $this->newCurrentStockAlt = $newCurrentStockAlt;
        $this->user = $user;
        $this->project = $project;
        $this->originalId = $originalId;
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
    
    public function getNote(): ?string
    {
        return $this->note;
    }
    
    public function getMaterialLocation(): MaterialLocation
    {
        return $this->materialLocation;
    }
    
    public function getAmount(): float
    {
        return $this->amount ?? 0;
    }
    
    public function getAmountAlt(): ?float
    {
        return $this->amountAlt;
    }
    
    public function getNewCurrentStock(): float
    {
        return $this->newCurrentStock ?? 0;
    }
    
    public function getNewCurrentStockAlt(): ?float
    {
        return $this->newCurrentStockAlt;
    }
    
    public function getProject(): ?Project
    {
        return $this->project;
    }
    
    public function getDocument(): ?string
    {
        if ($this->document) {
            return File::fromArray($this->document)->getRelativePath();
        }
        return null;
    }
    
    public function setDocument(?File $document): void
    {
        $this->document = $document;
    }
    
    public function setUser(User $user): void
    {
        $this->user = $user;
    }
    
    public function setNote(?string $note): void
    {
        $this->note = $note;
    }
    
    public function getFiles(): array
    {
        // Not applicable
        return [];
    }
    
    public function getAllFiles(): array
    {
        // Not applicable
        return [];
    }
    
    public function addFile(File $fileToAdd): void
    {
        $this->document = $fileToAdd->toArray();
    }
    
    public function updateFile(File $file): void
    {
        // Not applicable
    }
    
    public function removeFile(File $file): void
    {
        // Not applicable
    }
    
    /**
     * @Groups({"list", "detail"})
     */
    public function getThumb(): ?string
    {
        // Not applicable
        return null;
    }
    
    /**
     * @Groups({"list", "detail"})
     */
    public function getThumbFile(): ?File
    {
        // Not applicable
        return null;
    }
    
    public function getUser(): string
    {
        return $this->user->getFullName();
    }
}
