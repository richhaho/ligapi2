<?php

declare(strict_types=1);


namespace App\Entity;


use App\Exceptions\Domain\InconsistentDataException;
use Doctrine\ORM\Mapping as ORM;
use Money\Money;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MaterialForWebRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="idx_materialId", columns={"company_id", "material_id"})})
 *
 */
class MaterialForWeb
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=255)
     */
    private string $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="customFields")
     */
    private Company $company;
    
    /**
     * @ORM\Column(type="string")
     */
    private string $materialId;
    
    /**
     * @ORM\Column(type="string")
     * @Groups({"list"})
     */
    private string $itemNumber;
    
    /**
     * @ORM\Column(type="string")
     * @Groups({"list"})
     */
    private string $name;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"list"})
     */
    private ?string $itemGroup;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"list"})
     */
    private ?string $mainLocation;
    
    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"list"})
     */
    private ?float $mainLocationStock;
    
    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"list"})
     */
    private ?float $minStock;
    
    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"list"})
     */
    private ?float $maxStock;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"list"})
     */
    private ?string $mainLocationLastChange;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"list"})
     */
    private ?string $orderStatus;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"list"})
     */
    private ?string $thumb;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"list"})
     */
    private ?string $mainSupplier;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"list"})
     */
    private ?string $mainSupplierOrderNumber;
    
    /**
     * @ORM\Column(type="money", nullable=true)
     * @Groups({"list"})
     */
    private ?Money $mainSupplierPurchasingPrice;
    
    /**
     * @ORM\Column(type="money", nullable=true)
     * @Groups({"list"})
     */
    private ?Money $sellingPrice;
    
    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"list"})
     */
    private ?float $orderAmount;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"list"})
     */
    private ?string $manufacturerNumber;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"list"})
     */
    private ?string $manufacturerName;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"list"})
     */
    private ?string $barcode;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"list"})
     */
    private ?string $unit;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"list"})
     */
    private ?string $permissionGroup;
    
    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"list"})
     */
    private ?float $additionalStock;
    
    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"list"})
     */
    private ?float $totalStock;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"list"})
     */
    private ?string $note;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"list"})
     */
    private ?string $usableTill;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"list"})
     */
    private ?string $unitAlt;
    
    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"list"})
     */
    private ?float $unitConversion;
    
    /**
     * @ORM\Column(type="boolean")
     * @Groups({"list"})
     */
    private bool $permanentInventory;
    
    /**
     * @ORM\Column(type="json", nullable=true)
     * @Groups({"list"})
     */
    private ?array $customFields;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"list"})
     */
    private ?string $additionalSearchTerms;
    
    /**
     * @ORM\Column(type="boolean")
     * @Groups({"list"})
     */
    private bool $isArchived;
    
    public function __construct(Material $material)
    {
        $this->id = Uuid::uuid4()->toString();
        $this->company = $material->getCompany();
        $this->materialId = $material->getId();
        $this->itemNumber = $material->getitemNumber();
        $this->name = $material->getName();
        $this->itemGroup = $material->getItemGroup() ? $material->getItemGroup()->getName() : null;
        $this->mainLocation = $material->getMainLocationLinkName();
        $this->mainLocationStock = $material->getMainLocationLink() ? $material->getMainLocationLink()->getCurrentStock() : null;
        $this->minStock = $material->getMainLocationLinkMinStock();
        $this->maxStock = $material->getMainLocationLinkMaxStock();
        $this->mainLocationLastChange = ($material->getMainLocationLink() && $material->getMainLocationLink()->getLastStockChange()) ? $material->getMainLocationLink()->getLastStockChange()->getCreatedAt()->format('Y-m-d') : null;
        $this->orderStatus = $material->getOrderStatus();
        $this->thumb = $material->getProfileImage();
        if ($material->getMainOrderSource()) {
            $this->mainSupplierPurchasingPrice = Money::EUR(((float) $material->getMainOrderSource()->getPrice()) * 100);
            $this->mainSupplierOrderNumber = $material->getMainOrderSource()->getOrderNumber();
            $this->mainSupplier = $material->getMainOrderSource()->getSupplier()->getName();
        }
        $this->sellingPrice = $material->getSellingPrice() ? Money::EUR(((float) $material->getSellingPrice()) * 100) : null;
        $this->orderAmount = $material->getOrderAmount();
        $this->manufacturerNumber = $material->getManufacturerNumber();
        $this->manufacturerName = $material->getManufacturerName();
        $this->barcode = $material->getBarcode();
        $this->unit = $material->getUnit();
        $this->permissionGroup = $material->getPermissionGroup() ? $material->getPermissionGroup()->getName() : null;
        $this->additionalStock = $material->getAdditionalStock();
        $this->totalStock = $material->getTotalStock();
        $this->note = $material->getNote();
        $this->usableTill = $material->getUsableTill();
        $this->unitAlt = $material->getUnitAlt();
        $this->customFields = $material->getCustomFields();
        $this->additionalSearchTerms = $material->getAdditionalSearchTerms();
        $this->unitConversion = $material->getUnitConversion();
        $this->permanentInventory = $material->isPermanentInventory();
        $this->isArchived = $material->getIsArchived();
    }
    
    public function getItemNumber(): string
    {
        return $this->itemNumber;
    }
    
    public function setItemNumber(string $itemNumber): void
    {
        $this->itemNumber = $itemNumber;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function setName(string $name): void
    {
        $this->name = $name;
    }
    
    public function getItemGroup(): ?string
    {
        return $this->itemGroup;
    }
    
    public function setItemGroup(?string $itemGroup): void
    {
        $this->itemGroup = $itemGroup;
    }
    
    public function getMainLocation(): ?string
    {
        return $this->mainLocation;
    }
    
    public function setMainLocation(?string $mainLocation): void
    {
        $this->mainLocation = $mainLocation;
    }
    
    public function getMainLocationStock(): ?float
    {
        return $this->mainLocationStock;
    }
    
    public function setMainLocationStock(?float $mainLocationStock): void
    {
        $this->mainLocationStock = $mainLocationStock;
    }
    
    public function getMinStock(): ?float
    {
        return $this->minStock;
    }
    
    public function setMinStock(?float $minStock): void
    {
        $this->minStock = $minStock;
    }
    
    public function getMaxStock(): ?float
    {
        return $this->maxStock;
    }
    
    public function setMaxStock(?float $maxStock): void
    {
        $this->maxStock = $maxStock;
    }
    
    public function getMainLocationLastChange(): ?string
    {
        return $this->mainLocationLastChange;
    }
    
    public function setMainLocationLastChange(?string $mainLocationLastChange): void
    {
        $this->mainLocationLastChange = $mainLocationLastChange;
    }
    
    public function getOrderStatus(): string
    {
        return $this->orderStatus;
    }
    
    public function setOrderStatus(string $orderStatus): void
    {
        $this->orderStatus = $orderStatus;
    }
    
    public function getThumb(): ?string
    {
        return $this->thumb;
    }
    
    public function setThumb(?string $thumb): void
    {
        $this->thumb = $thumb;
    }
    
    public function getMainSupplier(): ?string
    {
        return $this->mainSupplier;
    }
    
    public function setMainSupplier(?string $mainSupplier): void
    {
        $this->mainSupplier = $mainSupplier;
    }
    
    public function getMainSupplierOrderNumber(): ?string
    {
        return $this->mainSupplierOrderNumber;
    }
    
    public function setMainSupplierOrderNumber(?string $mainSupplierOrderNumber): void
    {
        $this->mainSupplierOrderNumber = $mainSupplierOrderNumber;
    }
    
    public function getMainSupplierPurchasingPrice(): ?float
    {
        if (!$this->mainSupplierPurchasingPrice || !$this->mainSupplierPurchasingPrice->getAmount()) {
            return null;
        }
        return ((int) $this->mainSupplierPurchasingPrice->getAmount()) / 100;
    }
    
    public function setMainSupplierPurchasingPrice(?float $mainSupplierPurchasingPrice): void
    {
        if ($mainSupplierPurchasingPrice === null) {
            $this->mainSupplierPurchasingPrice = null;
        } else {
            $this->mainSupplierPurchasingPrice = Money::EUR(intval(round($mainSupplierPurchasingPrice * 100)));
        }
        if ($mainSupplierPurchasingPrice < 0) {
            throw InconsistentDataException::forNegativePrice($this->mainSupplierOrderNumber, $mainSupplierPurchasingPrice);
        }
    }
    
    public function getSellingPrice(): ?float
    {
        if (!$this->sellingPrice || !$this->sellingPrice->getAmount()) {
            return null;
        }
        return ((int) $this->sellingPrice->getAmount()) / 100;
    }
    
    public function setSellingPrice(?float $sellingPrice): void
    {
        if ($sellingPrice === null) {
            $this->sellingPrice = null;
        } else {
            $this->sellingPrice = Money::EUR(intval(round($sellingPrice * 100)));
        }
    }
    
    public function getOrderAmount(): ?float
    {
        return $this->orderAmount;
    }
    
    public function setOrderAmount(?float $orderAmount): void
    {
        $this->orderAmount = $orderAmount;
    }
    
    public function getManufacturerNumber(): ?string
    {
        return $this->manufacturerNumber;
    }
    
    public function setManufacturerNumber(?string $manufacturerNumber): void
    {
        $this->manufacturerNumber = $manufacturerNumber;
    }
    
    public function getManufacturerName(): ?string
    {
        return $this->manufacturerName;
    }
    
    public function setManufacturerName(?string $manufacturerName): void
    {
        $this->manufacturerName = $manufacturerName;
    }
    
    public function getBarcode(): ?string
    {
        return $this->barcode;
    }
    
    public function setBarcode(?string $barcode): void
    {
        $this->barcode = $barcode;
    }
    
    public function getUnit(): ?string
    {
        return $this->unit;
    }
    
    public function setUnit(?string $unit): void
    {
        $this->unit = $unit;
    }
    
    public function getPermissionGroup(): ?string
    {
        return $this->permissionGroup;
    }
    
    public function setPermissionGroup(?string $permissionGroup): void
    {
        $this->permissionGroup = $permissionGroup;
    }
    
    public function getAdditionalStock(): ?float
    {
        return $this->additionalStock;
    }
    
    public function setAdditionalStock(?float $additionalStock): void
    {
        $this->additionalStock = $additionalStock;
    }
    
    public function getTotalStock(): ?float
    {
        return $this->totalStock;
    }
    
    public function setTotalStock(?float $totalStock): void
    {
        $this->totalStock = $totalStock;
    }
    
    public function getNote(): ?string
    {
        return $this->note;
    }
    
    public function setNote(?string $note): void
    {
        $this->note = $note;
    }
    
    public function getUsableTill(): ?string
    {
        return $this->usableTill;
    }
    
    public function setUsableTill(?string $usableTill): void
    {
        $this->usableTill = $usableTill;
    }
    
    public function getUnitAlt(): ?string
    {
        return $this->unitAlt;
    }
    
    public function setUnitAlt(?string $unitAlt): void
    {
        $this->unitAlt = $unitAlt;
    }
    
    public function getUnitConversion(): ?float
    {
        return $this->unitConversion;
    }
    
    public function setUnitConversion(?float $unitConversion): void
    {
        $this->unitConversion = $unitConversion;
    }
    
    public function isPermanentInventory(): bool
    {
        return $this->permanentInventory;
    }
    
    public function setPermanentInventory(bool $permanentInventory): void
    {
        $this->permanentInventory = $permanentInventory;
    }
    
    public function getCustomFields(): ?array
    {
        return $this->customFields;
    }
    
    public function setCustomFields(?array $customFields): void
    {
        $this->customFields = $customFields;
    }
    
    public function getAdditionalSearchTerms(): ?string
    {
        return $this->additionalSearchTerms;
    }
    
    public function setAdditionalSearchTerms(?string $additionalSearchTerms): void
    {
        $this->additionalSearchTerms = $additionalSearchTerms;
    }
}
