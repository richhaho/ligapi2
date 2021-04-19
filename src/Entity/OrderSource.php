<?php


namespace App\Entity;

use App\Entity\Data\AutoStatus;
use App\Event\Log;
use App\Exceptions\Domain\InconsistentDataException;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Money\Money;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * @ORM\Entity(repositoryClass="App\Repository\OrderSourceRepository")
 */
class OrderSource implements LoggableInterface, CompanyAwareInterface
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=255)
     * @Groups({"detail", "list", "shoppingCart", "orderdetails", "directorderdetails"})
     */
    private string $id;
    
    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private DateTimeImmutable $createdAt;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="orderSources")
     */
    private Company $company;
    
    /**
     * @ORM\Column(type="string")
     * @Log()
     * @Groups({"list", "detail", "orderdetails", "shoppingCart", "orderedMaterials", "directorderdetails"})
     */
    private string $orderNumber;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Log()
     * @Groups({"detail"})
     */
    private ?string $note;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Material", inversedBy="orderSources")
     * @Groups({"orderdetails", "shoppingCart"})
     */
    private Material $material;
    
    /**
     * @ORM\Column(type="integer")
     * @Log()
     * @Groups({"detail", "list", "shoppingCart", "orderdetails"})
     */
    private int $priority;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Supplier", fetch="EAGER", inversedBy="orderSources")
     * @Groups({"list", "detail", "shoppingCart", "orderedMaterials", "orderdetails", "directorderdetails"})
     */
    private Supplier $supplier;
    
    /**
     * @ORM\Column(type="float", nullable=true)
     * @Log()
     * @Groups({"detail", "shoppingCart", "orderedMaterials", "orderdetails"})
     */
    private ?float $amountPerPurchaseUnit;
    
    /**
     * @ORM\Column(type="money", nullable=true)
     * @Log()
     * @Groups({"detail", "list", "shoppingCart", "orderdetails", "directorderdetails"})
     */
    private ?Money $price = null;
    
    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"detail", "shoppingCart", "orderdetails"})
     */
    private ?DateTimeImmutable $lastPriceUpdate = null;
    
    /**
     * @ORM\Embedded(class="App\Entity\Data\AutoStatus", columnPrefix="autoStatus_")
     * @Log()
     * @Groups({"list", "detail"})
     */
    private ?AutoStatus $autoStatus = null;
    
    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"detail"})
     */
    private ?DateTimeImmutable $lastAutoSet = null;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MaterialOrderPosition", mappedBy="orderSource", cascade={"remove"})
     */
    private Collection $materialOrderPositions;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\DirectOrderPositionResult", mappedBy="orderSource")
     */
    private Collection $directOrderPositionResults;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PriceUpdate", mappedBy="orderSource")
     */
    private Collection $priceUpdates;
    
    public function __construct(string $orderNumber, int $priority, Material $material, Supplier $supplier, Company $company)
    {
        $this->id = Uuid::uuid4()->toString();
        $this->createdAt = new DateTimeImmutable();
        $this->company = $company;
        $this->material = $material;
        $this->supplier = $supplier;
        $this->orderNumber = $orderNumber;
        $this->priority = $priority;
        $this->materialOrderPositions = new ArrayCollection();
        $this->directOrderPositionResults = new ArrayCollection();
        $this->priceUpdates = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
    
    public function getMaterial(): Material
    {
        return $this->material;
    }
    
    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }
    
    public function getSupplier(): Supplier
    {
        return $this->supplier;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function getMaterialId(): string
    {
        return $this->material->getId();
    }

    public function getPriority(): int
    {
        return $this->priority;
    }
    
    public function getSupplierId(): string
    {
        return $this->supplier->getId();
    }
    
    public function getAmountPerPurchaseUnit(): ?float
    {
        return $this->amountPerPurchaseUnit;
    }
    
    public function getPrice(): ?float
    {
        if (!$this->price || !$this->price->getAmount()) {
            return null;
        }
        return $this->price ? $this->price->getAmount() / 100 : null;
    }
    
    public function getCompany(): Company
    {
        return $this->company;
    }
    
    public function setOrderNumber(string $orderNumber): self
    {
        $this->orderNumber = $orderNumber;
    
        return $this;
    }
    
    public function setNote(?string $note): self
    {
        $this->note = $note;
    
        return $this;
    }
    
    public function setPriority(int $priority): self
    {
        $this->priority = $priority;
    
        return $this;
    }
    
    public function setAmountPerPurchaseUnit(?float $amountPerPurchaseUnit): self
    {
        $this->amountPerPurchaseUnit = $amountPerPurchaseUnit;
    
        return $this;
    }
    
    public function setPrice(?float $price): void
    {
        if ($price === null) {
            $this->price = null;
        } else {
            $this->price = Money::EUR(intval(round($price * 100)));
        }
        if ($price < 0) {
            throw InconsistentDataException::forNegativePrice($this->orderNumber, $price);
        }
        $this->setLastPriceUpdate(new DateTimeImmutable());
    }
    
    public function setPriceAsMoney(?Money $price): void
    {
        $this->price = $price;
        $this->setLastPriceUpdate(new DateTimeImmutable());
    }
    
    public function getLastPriceUpdate(): ?string
    {
        return $this->lastPriceUpdate ? $this->lastPriceUpdate->format('Y-m-d') : null;
    }
    
    public function setLastPriceUpdate(?DateTimeImmutable $lastPriceUpdate): void
    {
        $this->lastPriceUpdate = $lastPriceUpdate;
    }
    
    public function getAutoStatus(): ?AutoStatus
    {
        return $this->autoStatus;
    }
    
    public function setAutoStatus(?AutoStatus $autoStatus): void
    {
        $this->setLastAutoSet(new DateTimeImmutable());
        $this->autoStatus = $autoStatus;
    }
    
    public function getLastAutoSet(): ?DateTimeImmutable
    {
        return $this->lastAutoSet;
    }
    
    public function setLastAutoSet(?DateTimeImmutable $lastAutoSet): void
    {
        $this->lastAutoSet = $lastAutoSet;
    }
    
    public function getLogData(): string
    {
        return $this->getId();
    }
    
    public function getMaterialOrderPositions()
    {
        return $this->materialOrderPositions;
    }
    
    /**
     * @Groups({"shoppingCart"})
     */
    public function getIsCheapest(): bool
    {
        $lowestPrice = 0;
        $cheapestId = '';
        $isCheapest = false;
        $this->material->getOrderSources()->map(function($orderSource) use (&$lowestPrice, &$cheapestId) {
            /** @var OrderSource $orderSource */
            if (!$orderSource->getPrice()) {
                return;
            }
            if ($lowestPrice === 0) {
                $lowestPrice = $orderSource->getPrice();
                $cheapestId = $orderSource->getId();
                return;
            }
            if ($lowestPrice > $orderSource->getPrice()) {
                $lowestPrice = $orderSource->getPrice();
                $cheapestId = $orderSource->getId();
            }
        });
        if ($cheapestId === $this->id) {
            $isCheapest = true;
        }
        return $isCheapest;
    }
    
    /**
     * @Groups({"shoppingCart"})
     */
    public function getSourcesCount(): int
    {
        return $this->material->getOrderSources()->count();
    }
    
    public function getDirectOrderPositionResults()
    {
        return $this->directOrderPositionResults;
    }
    
    public function getPriceUpdates(): iterable
    {
        return $this->priceUpdates;
    }
    
}
