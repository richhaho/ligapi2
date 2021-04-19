<?php


namespace App\Entity;


use App\Entity\Data\LocationCategory;
use App\Event\Log;
use App\Exceptions\Domain\InconsistentDataException;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MaterialLocationRepository")
 */
class MaterialLocation implements CompanyAwareInterface
{
    
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=255)
     * @Groups({"detail", "list", "stocktaking", "shoppingCart", "listStockChanges"})
     */
    private string $id;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $originalId = null;
    
    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private DateTimeImmutable $createdAt;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="materialLocations")
     */
    private Company $company;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Location", fetch="EAGER", inversedBy="materialLocations")
     * @Log()
     */
    private ?Location $location;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\StockChange", mappedBy="materialLocation")
     */
    private Collection $stockChanges;
    
    /**
     * @ORM\Embedded(class="App\Entity\Data\LocationCategory", columnPrefix="locationCategory_")
     * @Log()
     * @Groups({"list", "detail", "stocktaking"})
     */
    private LocationCategory $locationCategory;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Material", inversedBy="materialLocations")
     * @Groups({"listStockChanges", "stocktaking"})
     */
    private Material $material;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Project", inversedBy="materialLocations")
     * @Groups({"list", "detail", "stocktaking", "shoppingCart", "listStockChanges"})
     */
    private ?Project $project;
    
    /**
     * @ORM\Column(type="float", nullable=true)
     * @Log()
     * @Groups({"list", "detail", "stocktaking", "shoppingCart"})
     */
    private ?float $minStock = null;
    
    /**
     * @ORM\Column(type="float", nullable=true)
     * @Log()
     * @Groups({"list", "detail", "stocktaking", "shoppingCart"})
     */
    private ?float $maxStock = null;
    
    /**
     * @ORM\Column(type="float")
     * @Log()
     * @Groups({"list", "detail", "stocktaking", "shoppingCart"})
     */
    private float $currentStock;
    
    /**
     * @ORM\Column(type="float")
     * @Log()
     * @Groups({"list", "detail", "stocktaking", "shoppingCart"})
     */
    private float $currentStockAlt;
    
    public function __construct(
        Company $company,
        LocationCategory $locationCategory,
        Material $material,
        ?Location $location = null,
        ?Project $project = null,
        ?string $originalId = null
    )
    {
        $this->company = $company;
        $this->id = Uuid::uuid4()->toString();
        $this->createdAt = new DateTimeImmutable();
        $this->location = $location;
        $this->project = $project;
        $this->locationCategory = $locationCategory;
        $this->stockChanges = new ArrayCollection();
        $this->material = $material;
        $this->currentStock = 0;
        $this->currentStockAlt = 0;
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
    
    public function getLocation(): ?Location
    {
        return $this->location;
    }
    
    /**
     * @Groups({"list", "detail", "stocktaking", "listStockChanges", "shoppingCart"})
     */
    public function getName(): ?string
    {
        if (!$this->location) {
            return null;
        }
        return $this->location->getName();
    }
    
    public function getLocationId(): ?string
    {
        if (!$this->location) {
            return null;
        }
        return $this->location->getId();
    }
    
    public function getLocationCategory(): string
    {
        return $this->locationCategory->getValue();
    }
    
    public function getMaterial(): Material
    {
        return $this->material;
    }
    
//    /**
//     * @Groups({"stocktaking"})
//     */
//    public function getLastStockChangeDate(): ?string
//    {
//        $lastStockChange = $this->getLastStockChange();
//        if ($lastStockChange) {
//            return $lastStockChange->getCreatedAt()->format('Y-m-d');
//        }
//        return null;
//    }

    public function getMinStock(): float
    {
        return $this->minStock ?? 0;
    }
    
    public function getCurrentStock(): float
    {
        return $this->currentStock;
    }

    public function getCurrentStockAlt(): float
    {
        return $this->currentStockAlt;
    }
    
    
    public function setMinStock(?float $minStock): self
    {
        $this->minStock = $minStock;
    
        return $this;
    }
    
    public function setCurrentStock(float $currentStock): self
    {
        if ($currentStock < 0) {
            throw InconsistentDataException::forNegativeStock($this->getName(), $currentStock);
        }
        $this->currentStock = $currentStock;
    
        return $this;
    }
    
    public function setCurrentStockAlt(?float $currentStockAlt): self
    {
        if ($currentStockAlt < 0) {
            throw InconsistentDataException::forNegativeStock($this->getMaterial()->getitemNumber() . ' | ' . $this->getName() . ' (Füllmenge)', $currentStockAlt);
        }
        $this->currentStockAlt = $currentStockAlt ?? 0;
        
        return $this;
    }
    
    public function setLocationCategory(LocationCategory $locationCategory)
    {
        $this->locationCategory = $locationCategory;
    }
    
    public function setLocation(?Location $location)
    {
        $this->location = $location;
    }
    
    public function getLogData(): string
    {
        return $this->getName();
    }
    
    public function getCompany(): Company
    {
        return $this->company;
    }
    
    /**
     * @Groups({"stocktaking"})
     */
    public function getLastStockChange(): ?StockChange
    {
        $iterator = $this->stockChanges->getIterator();
        $iterator->uasort(function (StockChange $a, StockChange  $b) {
            return ( $a->getCreatedAt() < $b->getCreatedAt()) ? 1 : -1;
        });
        /** @var StockChange $lastStockChange */
        $lastStockChange = (new ArrayCollection(iterator_to_array($iterator)))->first();
        if ($lastStockChange) {
            return $lastStockChange;
        }
        return null;
    }
    
    public function getStockChanges()
    {
        return $this->stockChanges;
    }
    
    public function getMaxStock(): ?float
    {
        return $this->maxStock;
    }
    
    public function setMaxStock(?float $maxStock): void
    {
        $this->maxStock = $maxStock;
    }
    
    public function getProject(): ?Project
    {
        return $this->project;
    }
    
}
