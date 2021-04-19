<?php


namespace App\Entity;

use App\Entity\Data\AutoStatus;
use App\Entity\Data\ConsignmentItemStatus;
use App\Entity\Data\File;
use App\Entity\Data\LocationCategory;
use App\Entity\Data\OrderStatus;
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
 * @ORM\Entity(repositoryClass="App\Repository\MaterialRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="idx_itemNumber", columns={"company_id", "item_number"})})
 */
class Material implements CompanyAwareInterface, FileAwareInterface, LoggableInterface, SearchableInterface, PermissionAwareInterface, DeleteUpdateAwareInterface
{
    const PERMISSION = 'material';
    
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=255)
     * @Groups({"list", "detail", "stocktaking", "orderdetails", "directorderdetails", "shoppingCart", "orderedMaterials", "listStockChanges"})
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
     * @ORM\Column(type="boolean")
     * @Groups({"list", "detail", "shoppingCart"})
     */
    private bool $permanentInventory;
    
    /**
     * @ORM\Column(type="string", length=255)
     * @Log()
     * @Groups({"list", "detail", "stocktaking", "orderdetails", "directorderdetails", "shoppingCart", "orderedMaterials", "listStockChanges"})
     */
    private string $name;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     * @Log()
     * @Groups({"list", "detail"})
     */
    private ?string $note;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="materials")
     */
    private Company $company;
    
    /**
     * @ORM\Column(type="string", length=50)
     * @Log()
     * @Groups({"list", "detail", "stocktaking", "orderdetails", "directorderdetails", "shoppingCart", "orderedMaterials", "listStockChanges"})
     */
    private string $itemNumber;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Log()
     * @Groups({"list", "detail", "shoppingCart", "orderedMaterials"})
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
     * @Groups({"list", "detail", "listStockChanges"})
     */
    private ?string $barcode = null;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Log()
     * @Groups({"list", "detail", "orderdetails", "directorderdetails", "shoppingCart", "orderedMaterials", "stocktaking"})
     */
    private ?string $unit = null;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Log()
     * @Groups({"list", "detail", "shoppingCart", "orderedMaterials", "stocktaking"})
     */
    private ?string $unitAlt = null;
    
    /**
     * @ORM\Column(type="float", nullable=true)
     * @Log()
     * @Groups({"detail", "list", "shoppingCart"})
     */
    private ?float $orderAmount = null;
    
    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Log()
     * @Groups({"list", "detail"})
     */
    private ?DateTimeImmutable $usableTill = null;
    
    /**
     * @ORM\Column(type="float", nullable=true)
     * @Log()
     * @Groups({"list", "detail", "orderedMaterials", "stocktaking"})
     */
    private ?float $unitConversion = null;
    
    /**
     * @ORM\Embedded(class="App\Entity\Data\OrderStatus", columnPrefix="orderStatus_")
     * @Log()
     * @Groups({"list", "detail", "shoppingCart"})
     */
    private ?OrderStatus $orderStatus;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Log()
     * @Groups({"detail", "shoppingCart"})
     */
    private ?string $orderStatusNote = null;
    
    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"list", "detail", "shoppingCart", "orderedMaterials"})
     */
    private ?DateTimeImmutable $orderStatusChangeDateToOrder = null;
    
    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"list", "detail", "shoppingCart", "orderedMaterials"})
     */
    private ?DateTimeImmutable $orderStatusChangeDateOnItsWay = null;
    
    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"list", "detail", "shoppingCart", "orderedMaterials"})
     */
    private ?DateTimeImmutable $orderStatusChangeDateAvailable = null;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="orderStatusChangeMaterialsToOrder")
     * @Groups({"shoppingCart", "orderedMaterials"})
     */
    private ?User $orderStatusChangeUserToOrder = null;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="orderStatusChangeMaterialsOnItsWay")
     * @Groups({"orderedMaterials"})
     */
    private ?User $orderStatusChangeUserOnItsWay = null;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="orderStatusChangeMaterialsAvailable")
     */
    private ?User $orderStatusChangeUserAvailable = null;
    
    /**
     * @ORM\OneToOne(targetEntity="App\Entity\MaterialOrderPosition", inversedBy="material")
     * @Groups({"orderedMaterials"})
     */
    private ?MaterialOrderPosition $lastMaterialOrderPosition = null;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MaterialLocation", mappedBy="material", fetch="EAGER", cascade={"persist"})
     * @Groups({"detail", "list"})
     */
    private Collection $materialLocations;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\OrderSource", fetch="EAGER", mappedBy="material")
     * @Groups({"list", "detail"})
     */
    private Collection $orderSources;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ItemGroup", fetch="EAGER", inversedBy="materials")
     * @Log()
     * @Groups({"list", "detail"})
     */
    private ?ItemGroup $itemGroup = null;
    
    /**
     * @ORM\Column(type="money", nullable=true)
     * @Log()
     * @Groups({"list", "detail"})
     */
    private ?Money $sellingPrice = null;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PermissionGroup", fetch="EAGER", inversedBy="materials")
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
     * @ORM\Column(type="string", nullable=true)
     * @Log()
     * @Groups({"detail"})
     */
    private ?string $autoSearchTerm = null;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Supplier", fetch="EAGER", inversedBy="autoMaterials")
     * @Log()
     * @Groups({"list", "detail"})
     */
    private ?Supplier $autoSupplier = null;
    
    /**
     * @ORM\Embedded(class="App\Entity\Data\AutoStatus", columnPrefix="autoStatus_")
     * @Log()
     * @Groups({"list", "detail"})
     */
    private ?AutoStatus $autoStatus = null;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Task", fetch="EAGER", mappedBy="material")
     * @Groups({"detail"})
     */
    private Collection $tasks;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ConsignmentItem", mappedBy="material")
     */
    private Collection $consignmentItems;
    
    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private ?array $altScannerIds = null;
    
    public function __construct(string $itemNumber, string $name, Company $company)
    {
        $this->company = $company;
        $this->createdAt = new DateTimeImmutable();
        $this->id = Uuid::uuid4()->toString();
        $this->itemNumber = $itemNumber;
        $this->name = $name;
        $this->materialLocations = new ArrayCollection();
        $this->orderSources = new ArrayCollection();
        $this->orderStatus = OrderStatus::available();
        $this->isArchived = false;
        $this->deleted = false;
        $this->permanentInventory = false;
        $this->files = [];
        $this->tasks = new ArrayCollection();
        $this->consignmentItems = new ArrayCollection();
    }
    
    /**
     * @return MaterialLocation[]|Collection
     */
    public function getMaterialLocations()
    {
        $filterdLocations = $this->materialLocations->filter(
            function(MaterialLocation $materialLocation) {
                if ($materialLocation->getProject() && $materialLocation->getProject()->getDeleted()) {
                    return false;
                }
                return true;
            }
        );
        return array_values($filterdLocations->toArray());
    }
    
    public function addMaterialLocation(MaterialLocation $materialLocation)
    {
        $this->materialLocations->add($materialLocation);
    }
    
    public function getOrderSources(): Collection
    {
        return $this->orderSources;
    }
    
    public function getOrderSourceWithSupplier(Supplier $supplier): ?OrderSource
    {
        /** @var OrderSource $orderSource */
        foreach ($this->orderSources as $orderSource) {
            if ($orderSource->getSupplier() === $supplier) {
                return $orderSource;
            }
        }
        return null;
    }
    
    public function addOrderSource(OrderSource $orderSource)
    {
        $this->orderSources->add($orderSource);
    }
    
    /**
     * @Groups({"shoppingCart"})
     */
    public function getMainLocationLink(): ?MaterialLocation
    {
        $mainLocation =  $this->materialLocations->filter(
            function(MaterialLocation $materialLocation) {
                return $materialLocation->getLocationCategory() === LocationCategory::main()->getValue();
            }
        )->first();
        return $mainLocation ? $mainLocation : null;
    }
    
    public function getMainLocationLinkName(): ?string
    {
        /** @var MaterialLocation $mainLocation */
        $mainLocation =  $this->materialLocations->filter(
            function(MaterialLocation $materialLocation) {
                return $materialLocation->getLocationCategory() === LocationCategory::main()->getValue();
            }
        )->first();
        return $mainLocation ? $mainLocation->getName() : '';
    }
    
    public function getMainLocationLinkMinStock(): float
    {
        /** @var MaterialLocation $mainLocation */
        $mainLocation =  $this->materialLocations->filter(
            function(MaterialLocation $materialLocation) {
                return $materialLocation->getLocationCategory() === LocationCategory::main()->getValue();
            }
        )->first();
        return $mainLocation ? $mainLocation->getMinStock() : 0;
    }
    
    public function getMainLocationLinkMaxStock(): ?float
    {
        /** @var MaterialLocation $mainLocation */
        $mainLocation =  $this->materialLocations->filter(
            function(MaterialLocation $materialLocation) {
                return $materialLocation->getLocationCategory() === LocationCategory::main()->getValue();
            }
        )->first();
        return $mainLocation ? $mainLocation->getMaxStock() : 0;
    }
    
    public function getMainOrderSource(): ?OrderSource
    {
        $mainOrderSource = $this->orderSources->filter(
            function(OrderSource $orderSource) {
                return $orderSource->getPriority() === 1;
            }
        )->first();
        return $mainOrderSource ? $mainOrderSource : null;
    }
    
    public function getMainOrderSourceOrderNumber(): string
    {
        /** @var OrderSource $mainOrderSource */
        $mainOrderSource = $this->orderSources->filter(
            function(OrderSource $orderSource) {
                return $orderSource->getPriority() === 1;
            }
        )->first();
        return $mainOrderSource ? $mainOrderSource->getOrderNumber() : '';
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
    
    public function getNote(): ?string
    {
        return $this->note;
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
    
    public function getUnit(): ?string
    {
        return $this->unit;
    }
    
    public function getUnitAlt(): ?string
    {
        return $this->unitAlt;
    }
    
    public function getOrderAmount(): ?float
    {
        return $this->orderAmount;
    }
    
    public function getUsableTill(): ?string
    {
        return $this->usableTill ? $this->usableTill->format('Y-m-d') : null;
    }
    
    public function getUnitConversion(): ?float
    {
        return $this->unitConversion;
    }
    
    public function getOrderStatus(): ?string
    {
        return $this->orderStatus->getValue();
    }
    
    public function getOrderStatusChangeDateToOrder(): ?DateTimeImmutable
    {
        return $this->orderStatusChangeDateToOrder;
    }
    
    public function getOrderStatusChangeDateOnItsWay(): ?DateTimeImmutable
    {
        return $this->orderStatusChangeDateOnItsWay;
    }
    
    public function getOrderStatusChangeDateAvailable(): ?DateTimeImmutable
    {
        return $this->orderStatusChangeDateAvailable;
    }
    
    public function getItemGroup(): ?ItemGroup
    {
        return $this->itemGroup;
    }
    
    public function getSellingPrice(): ?float
    {
        return $this->sellingPrice ? ((int) $this->sellingPrice->getAmount()) / 100 : null;
    }
    
    public function setName(string $name): void
    {
        $this->name = $name;
    }
    
    public function annotate(?string $note): void
    {
        $this->note = $note;
    }
    
    public function setitemNumber(string $itemNumber): void
    {
        $this->itemNumber = $itemNumber;
    }
    
    public function setManufacturerNumber(?string $manufacturerNumber): void
    {
        $this->manufacturerNumber = $manufacturerNumber;
    }
    
    public function setManufacturerName(?string $manufacturerName): void
    {
        $this->manufacturerName = $manufacturerName;
    }
    
    public function setBarcode(?string $barcode): void
    {
        $this->barcode = $barcode;
    }
    
    public function setUnit(?string $unit): void
    {
        $this->unit = $unit;
    }
    
    public function setUnitAlt(?string $unitAlt): void
    {
        $this->unitAlt = $unitAlt;
    }
    
    public function setOrderAmount(?float $orderAmount): void
    {
        $this->orderAmount = $orderAmount;
    }
    
    public function setUsableTill(?DateTimeImmutable $usableTill): void
    {
        $this->usableTill = $usableTill;
    }
    
    public function setUnitConversion(?float $unitConversion): void
    {
        $this->unitConversion = $unitConversion;
    }
    
    public function updateOrderStatus(?OrderStatus $orderStatus, User $user): void
    {
        if ($this->orderStatus->getValue() !== $orderStatus->getValue()) {
            switch ($orderStatus->getValue()) {
                case OrderStatus::toOrder()->getValue():
                    $this->orderStatusChangeDateToOrder = new DateTimeImmutable();
                    $this->orderStatusChangeUserToOrder = $user;
                    break;
                case OrderStatus::onItsWay()->getValue():
                    $this->orderStatusChangeDateOnItsWay = new DateTimeImmutable();
                    $this->orderStatusChangeUserOnItsWay = $user;
                    break;
                case OrderStatus::available()->getValue():
                    $this->orderStatusChangeDateAvailable = new DateTimeImmutable();
                    $this->orderStatusChangeUserAvailable = $user;
                    break;
            }
        }
        $this->orderStatus = $orderStatus;
    }
    
    public function setItemGroup(?ItemGroup $itemGroup): void
    {
        $this->itemGroup = $itemGroup;
    }
    
    public function setSellingPrice(?Money $sellingPrice): void
    {
        if ($sellingPrice && $sellingPrice->getAmount() < 0) {
            throw InconsistentDataException::forNegativePrice($this->name, (int) $sellingPrice->getAmount() / 100);
        }
        $this->sellingPrice = $sellingPrice;
    }
    
//    public function setOrderNote(?string $orderNote): void
//    {
//        $this->orderNote = $orderNote;
//
//        return $this;
//    }

    public function getIsArchived(): bool
    {
        return $this->isArchived;
    }
    
    public function setIsArchived(bool $isArchived): void
    {
        $this->isArchived = $isArchived;
    }
    
    public function getPermissionGroup(): ?PermissionGroup
    {
        return $this->permissionGroup;
    }
    
    public function setPermissionGroup(?PermissionGroup $permissionGroup): void
    {
        $this->permissionGroup = $permissionGroup;
    }
    
    public function usesPermanentInventory(): bool
    {
        return $this->permanentInventory;
    }
    
    public function setPermanentInventory(bool $permanentInventory): void
    {
        $this->permanentInventory = $permanentInventory;
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
    
    public function isPermanentInventory(): bool
    {
        return $this->permanentInventory;
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
    
    public function getAutoSearchTerm(): ?string
    {
        return $this->autoSearchTerm;
    }
    
    public function setAutoSearchTerm(?string $autoSearchTerm): void
    {
        $this->autoSearchTerm = $autoSearchTerm;
    }
    
    public function getAutoSupplier(): ?Supplier
    {
        return $this->autoSupplier;
    }
    
    public function setAutoSupplier(?Supplier $autoSupplier): void
    {
        $this->autoSupplier = $autoSupplier;
    }
    
    public function getAutoStatus(): ?AutoStatus
    {
        return $this->autoStatus;
    }
    
    public function setAutoStatus(?AutoStatus $autoStatus): void
    {
        $this->autoStatus = $autoStatus;
    }
    
    public function getOrderStatusNote(): ?string
    {
        return $this->orderStatusNote;
    }
    
    public function setOrderStatusNote(?string $orderStatusNote): void
    {
        $this->orderStatusNote = $orderStatusNote;
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
    
    /**
     * @Groups({"materialconsignmentdetails"})
     */
    public function getOpenConsignmentItems(): iterable
    {
        $filterdConsignmentItems = $this->consignmentItems->filter(
            function(ConsignmentItem $consignmentItem) {
                if ($consignmentItem->getConsignmentItemStatus() === ConsignmentItemStatus::complete()->getValue()) {
                    return false;
                }
                if ($consignmentItem->getConsignment()->getProject() && $consignmentItem->getConsignment()->getProject()->getDeleted()) {
                    return false;
                }
                return true;
            }
        );
        return array_values($filterdConsignmentItems->toArray());
    }
    
    public function getSearchableText(): string
    {
        $searchableText = $this->name . ' | ' . $this->manufacturerName . $this->manufacturerNumber;
        
        /** @var MaterialLocation $materialLocation */
        foreach ($this->materialLocations as $materialLocation) {
            $searchableText .= $materialLocation->getName();
        }
        
        return $searchableText;
    }
    
    public function getOrderStatusChangeUserToOrder(): ?User
    {
        return $this->orderStatusChangeUserToOrder;
    }
    
    public function getOrderStatusChangeUserOnItsWay(): ?User
    {
        return $this->orderStatusChangeUserOnItsWay;
    }
    
    public function getOrderStatusChangeUserAvailable(): ?User
    {
        return $this->orderStatusChangeUserAvailable;
    }
    
    public function getLastMaterialOrderPosition(): ?MaterialOrderPosition
    {
        return $this->lastMaterialOrderPosition;
    }
    
    public function setLastMaterialOrderPosition(?MaterialOrderPosition $lastMaterialOrderPosition): void
    {
        $this->lastMaterialOrderPosition = $lastMaterialOrderPosition;
    }
    
    /**
     * @Groups({"detail"})
     */
    public function getTasks()
    {
        $tasks = $this->tasks->filter(function(Task $task) {
            return $task->getTaskStatus() === 'open';
        });
        return array_values($tasks->toArray());
    }
    
    /**
     * @Groups({"list", "detail"})
     */
    public function getNextTask()
    {
        $iterator = $this->tasks->filter(function(Task $task) {
            return $task->getTaskStatus() === 'open' && $task->getStartDateAsDateTime() < new DateTimeImmutable();
        })->getIterator();
        $iterator->uasort(function (Task $a, Task $b) {
            return ( is_null($a->getDueDate()) OR $a->getDueDate() < $b->getDueDate()) ? 1 : -1;
        });
        return (new ArrayCollection(iterator_to_array($iterator)))->first();
    }
    
    public function getPermissionType(): string
    {
        return self::PERMISSION;
    }
    
    public function getAllTasks(): iterable
    {
        return $this->tasks;
    }
    
    /**
     * @Groups({"scannerResult"})
     */
    public function getType(): string
    {
        return 'material';
    }
    
    public function setAltScannerIds(?array $altScannerIds): void
    {
        $this->altScannerIds = $altScannerIds;
    }
    
    public function getAltScannerIds(): ?array
    {
        return $this->altScannerIds;
    }
    
    public function getAdditionalStock(): float
    {
        $additionalStock = 0;
        /** @var MaterialLocation $materialLocation */
        foreach ($this->materialLocations as $materialLocation) {
            if ($materialLocation->getLocationCategory() === LocationCategory::additional()) {
                $additionalStock += $materialLocation->getCurrentStock();
            }
        }
        return $additionalStock;
    }
    
    /**
     * @Groups({"list", "detail"})
     */
    public function getTotalStock()
    {
        $totalStock = 0;
        /** @var MaterialLocation $materialLocation */
        foreach ($this->materialLocations as $materialLocation) {
            $totalStock += $materialLocation->getCurrentStock();
        }
        return $totalStock;
    }
    
    public function getAdditionalSearchTerms(): string
    {
        $searchTerms = '';
        
        /** @var MaterialLocation $materialLocation */
        foreach ($this->materialLocations as $materialLocation) {
            if ($materialLocation->getLocationCategory() === LocationCategory::additional()->getValue()) {
                $searchTerms .= $materialLocation->getName();
            }
        }
    
        /** @var OrderSource $orderSource */
        foreach ($this->orderSources as $orderSource) {
            if ($orderSource->getPriority() !== 1) {
                $searchTerms .= $orderSource->getOrderNumber();
                $searchTerms .= $orderSource->getSupplier()->getName();
            }
        }
        
        return $searchTerms;
    }
    
    /**
     * @Groups({"list", "detail"})
     */
    public function getOpenConsignedAmount(): float
    {
        $consignedAmountOpen = 0;
    
        /** @var ConsignmentItem $consignmentItem */
        foreach ($this->consignmentItems as $consignmentItem) {
            $consignedAmountOpen += $consignmentItem->getAmount() - $consignmentItem->getConsignedAmount();
        }
        
        return $consignedAmountOpen;
    }
    
    /**
     * @Groups({"list", "detail"})
     */
    public function getConsignmentRemainingAmount(): float
    {
        return $this->getTotalStock() - $this->getOpenConsignedAmount();
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
