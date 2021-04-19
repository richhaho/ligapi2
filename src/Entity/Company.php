<?php


namespace App\Entity;


use App\Entity\Data\AppSettings;
use App\Entity\Data\File;
use App\Entity\Data\PaymentCycleType;
use App\Entity\Data\PaymentType;
use App\Exceptions\Domain\InconsistentDataException;
use App\Exceptions\Domain\UnsupportedMethodException;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity()
 */
class Company implements FileAwareInterface
{
    /**
     * @ORM\Id()
     * @Groups({"detail", "list"})
     * @ORM\Column(type="string", length=255)
     */
    private string $id;
    
    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private DateTimeImmutable $createdAt;
    
    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Groups({"detail", "list"})
     */
    private string $name;
    
    /**
     * @ORM\Column(type="json")
     */
    private array $files;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"detail", "list"})
     */
    private ?string $street = null;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private bool $termsAccepted = false;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"detail", "list"})
     */
    private ?string $zip = null;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"detail", "list"})
     */
    private ?string $phone = null;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"detail", "list"})
     */
    private ?string $fax = null;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"detail", "list"})
     */
    private ?string $city = null;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"detail", "list"})
     */
    private ?string $invoiceEmail = null;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"detail", "list"})
     */
    private ?string $orderEmail = null;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"detail", "list"})
     */
    private ?string $addressLine1 = null;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"detail", "list"})
     */
    private ?string $addressLine2 = null;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"detail", "list"})
     */
    private ?string $website = null;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"detail", "list"})
     */
    private ?int $userAmount = null;
    
    /**
     * @ORM\Embedded(class="App\Entity\Data\PaymentType", columnPrefix="paymentType_")
     * @Groups({"list", "detail"})
     */
    private ?PaymentType $paymentType = null;
    
    /**
     * @ORM\Embedded(class="App\Entity\Data\PaymentCycleType", columnPrefix="paymentCycleType_")
     * @Groups({"list", "detail"})
     */
    private ?PaymentCycleType $paymentCycle = null;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"detail", "list"})
     */
    private ?string $country = null;
    
    /**
     * @ORM\Column(type="json")
     */
    private array $collectionLocations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Keyy", mappedBy="company")
     */
    private Collection $keyys;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MaterialOrder", mappedBy="company")
     */
    private Collection $materialOrders;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Customer", mappedBy="company")
     */
    private Collection $customers;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Tool", mappedBy="company")
     */
    private Collection $tools;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Material", mappedBy="company")
     */
    private Collection $materials;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Location", mappedBy="company")
     */
    private Collection $locations;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\OrderSource", mappedBy="company")
     */
    private Collection $orderSources;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ItemGroup", mappedBy="company")
     */
    private Collection $itemGroups;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Supplier", mappedBy="company")
     */
    private Collection $suppliers;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User", mappedBy="company")
     */
    private Collection $users;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SearchIndex", mappedBy="company")
     */
    private Collection $searchIndexes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PermissionGroup", mappedBy="company")
     */
    private Collection $permissionGroups;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\StockChange", mappedBy="company")
     */
    private Collection $stockChanges;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Project", mappedBy="company")
     */
    private Collection $projects;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Task", mappedBy="company")
     */
    private Collection $tasks;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Consignment", mappedBy="company")
     */
    private Collection $consignments;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ConsignmentItem", mappedBy="company")
     */
    private Collection $consignmentItems;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CustomField", mappedBy="company")
     */
    private Collection $customFields;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\DirectOrder", mappedBy="company")
     */
    private Collection $directOrders;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\DirectOrderPosition", mappedBy="company")
     */
    private Collection $directOrderPositions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\DirectOrderPositionResult", mappedBy="company")
     */
    private Collection $directOrderPositionResults;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\GridState", mappedBy="company")
     */
    private Collection $gridStates;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MaterialLocation", mappedBy="company")
     */
    private Collection $materialLocations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MaterialOrderPosition", mappedBy="company")
     */
    private Collection $materialOrderPositions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\OwnerChange", mappedBy="company")
     */
    private Collection $ownerChanges;

    /**
     * @ORM\OneToMany(targetEntity="PdfDocumentType", mappedBy="company")
     */
    private Collection $pdfDocumentTypes;
    
    /**
     * @ORM\Column(type="integer")
     * @Groups({"detail"})
     */
    private int $currentMaterialLabel;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"detail"})
     */
    private ?string $customMaterialName = null;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"detail"})
     */
    private ?string $customToolName = null;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"detail"})
     */
    private ?string $customKeyyName = null;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"detail"})
     */
    private ?string $customMaterialsName = null;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"detail"})
     */
    private ?string $customToolsName = null;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"detail"})
     */
    private ?string $customKeyysName = null;
    
    /**
     * @ORM\Column(type="json", nullable=true)
     * @Groups({"detail"})
     */
    private ?array $appSettings = null;
    
    public function __construct(string $name, bool $termsAccepted)
    {
        if (!$termsAccepted) {
            throw InconsistentDataException::forTermsNotAccepted();
        }
        $this->id = Uuid::uuid4()->toString();
        $this->createdAt = new DateTimeImmutable();
        $this->name = $name;
        $this->termsAccepted = $termsAccepted;
        $this->files = [];
        $this->materials = new ArrayCollection();
        $this->locations = new ArrayCollection();
        $this->keyys = new ArrayCollection();
        $this->tools = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->suppliers = new ArrayCollection();
        $this->itemGroups = new ArrayCollection();
        $this->tasks = new ArrayCollection();
        $this->searchIndexes = new ArrayCollection();
        $this->orderSources = new ArrayCollection();
        $this->permissionGroups = new ArrayCollection();
        $this->stockChanges = new ArrayCollection();
        $this->projects = new ArrayCollection();
        $this->materialOrders = new ArrayCollection();
        $this->pdfDocumentTypes = new ArrayCollection();
        $this->customers = new ArrayCollection();
        $this->consignments = new ArrayCollection();
        $this->consignmentItems = new ArrayCollection();
        $this->customFields = new ArrayCollection();
        $this->directOrders = new ArrayCollection();
        $this->directOrderPositions = new ArrayCollection();
        $this->directOrderPositionResults = new ArrayCollection();
        $this->gridStates = new ArrayCollection();
        $this->materialLocations = new ArrayCollection();
        $this->materialOrderPositions = new ArrayCollection();
        $this->ownerChanges = new ArrayCollection();
        $this->currentMaterialLabel = 1;
        $this->collectionLocations = [];
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

    public function getKeyys(): Collection
    {
        return $this->keyys;
    }
    
    public function assignName(string $name): self
    {
        $this->name = $name;
        
        return $this;
    }
    
    public function getCurrentMaterialLabel(): int
    {
        return $this->currentMaterialLabel;
    }
    
    public function setCurrentMaterialLabel(int $currentMaterialLabel): void
    {
        $this->currentMaterialLabel = $currentMaterialLabel;
    }
    
    public function setName(string $name): void
    {
        $this->name = $name;
    }
    
    public function getCollectionLocations(): array
    {
        return $this->collectionLocations;
    }
    
    public function setCollectionLocations(array $collectionLocations): void
    {
        $this->collectionLocations = $collectionLocations;
    }
    
    public function getUsers()
    {
        return $this->users;
    }
    
    public function getFullUserNames(): array
    {
        $userNames = [];
        /** @var User $user */
        foreach ($this->users as $user) {
            $userNames[] = $user->getFullName();
        }
        return $userNames;
    }
    
    public function getCountry(): ?string
    {
        return $this->country;
    }
    
    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }
    
    public function getAppSettings(): ?array
    {
        if (!$this->appSettings) {
            return null;
        }
        return AppSettings::fromArray($this->appSettings)->toArray();
    }
    
    public function setAppSettings(?AppSettings $appSettings): void
    {
        $this->appSettings = $appSettings->toArray();
    }
    
    public function getCustomMaterialName(): ?string
    {
        return $this->customMaterialName;
    }
    
    public function setCustomMaterialName(?string $customMaterialName): void
    {
        $this->customMaterialName = $customMaterialName;
    }
    
    public function getCustomToolName(): ?string
    {
        return $this->customToolName;
    }
    
    public function setCustomToolName(?string $customToolName): void
    {
        $this->customToolName = $customToolName;
    }
    
    public function getCustomKeyyName(): ?string
    {
        return $this->customKeyyName;
    }
    
    public function setCustomKeyyName(?string $customKeyyName): void
    {
        $this->customKeyyName = $customKeyyName;
    }
    
    public function getCustomMaterialsName(): ?string
    {
        return $this->customMaterialsName;
    }
    
    public function setCustomMaterialsName(?string $customMaterialsName): void
    {
        $this->customMaterialsName = $customMaterialsName;
    }
    
    public function getCustomToolsName(): ?string
    {
        return $this->customToolsName;
    }
    
    public function setCustomToolsName(?string $customToolsName): void
    {
        $this->customToolsName = $customToolsName;
    }
    
    public function getCustomKeyysName(): ?string
    {
        return $this->customKeyysName;
    }
    
    public function setCustomKeyysName(?string $customKeyysName): void
    {
        $this->customKeyysName = $customKeyysName;
    }
    
    public function getStreet(): ?string
    {
        return $this->street;
    }
    
    public function setStreet(?string $street): void
    {
        $this->street = $street;
    }
    
    public function getZip(): ?string
    {
        return $this->zip;
    }
    
    public function setZip(?string $zip): void
    {
        $this->zip = $zip;
    }
    
    public function getPhone(): ?string
    {
        return $this->phone;
    }
    
    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }
    
    public function getFax(): ?string
    {
        return $this->fax;
    }
    
    public function setFax(?string $fax): void
    {
        $this->fax = $fax;
    }
    
    public function getCity(): ?string
    {
        return $this->city;
    }
    
    public function setCity(?string $city): void
    {
        $this->city = $city;
    }
    
    public function getInvoiceEmail(): ?string
    {
        return $this->invoiceEmail;
    }
    
    public function getWebsite(): ?string
    {
        return $this->website;
    }
    
    public function setInvoiceEmail(?string $invoiceEmail): void
    {
        $this->invoiceEmail = $invoiceEmail;
    }
    
    public function setWebsite(?string $website): void
    {
        $this->website = $website;
    }
    
    public function getTools()
    {
        return $this->tools;
    }
    
    public function getMaterials()
    {
        return $this->materials;
    }
    
    public function getLocations()
    {
        return $this->locations;
    }
    
    public function getOrderSources()
    {
        return $this->orderSources;
    }
    
    public function getItemGroups()
    {
        return $this->itemGroups;
    }
    
    public function getSuppliers()
    {
        return $this->suppliers;
    }
    
    public function getPermissionGroups()
    {
        return $this->permissionGroups;
    }
    
    public function getStockChanges()
    {
        return $this->stockChanges;
    }
    
    public function getProjects()
    {
        return $this->projects;
    }
    
    public function getCustomers()
    {
        return $this->customers;
    }
    
    public function getTasks()
    {
        return $this->tasks;
    }
    
    public function getSearchIndexes()
    {
        return $this->searchIndexes;
    }
    
    public function getMaterialOrders(): Collection
    {
        return $this->materialOrders;
    }
    
    public function getConsignments()
    {
        return $this->consignments;
    }
    
    public function getConsignmentItems()
    {
        return $this->consignmentItems;
    }
    
    public function getCustomFields()
    {
        return $this->customFields;
    }
    
    public function getDirectOrders()
    {
        return $this->directOrders;
    }
    
    public function getDirectOrderPositions()
    {
        return $this->directOrderPositions;
    }
    
    public function getDirectOrderPositionResults()
    {
        return $this->directOrderPositionResults;
    }
    
    public function getGridStates()
    {
        return $this->gridStates;
    }
    
    public function getMaterialLocations()
    {
        return $this->materialLocations;
    }
    
    public function getMaterialOrderPositions()
    {
        return $this->materialOrderPositions;
    }
    
    public function getOwnerChanges()
    {
        return $this->ownerChanges;
    }
    
    public function getAddressArray(): array
    {
        $companyDataArray = [];
        $companyDataArray[] = $this->name;
        if ($this->street) {
            $companyDataArray[] = $this->street;
        }
        if ($this->zip) {
            $companyDataArray[] = $this->zip . ' ' . $this->city;
        }
        return $companyDataArray;
    }
    
    public function getLine(): string
    {
        return implode(" | ", $this->getAddressArray());
    }
    
    public function getOrderEmail(): ?string
    {
        return $this->orderEmail;
    }
    
    public function setOrderEmail(?string $orderEmail): void
    {
        $this->orderEmail = $orderEmail;
    }
    
    public function getAddressLine1(): ?string
    {
        return $this->addressLine1;
    }
    
    public function setAddressLine1(?string $addressLine1): void
    {
        $this->addressLine1 = $addressLine1;
    }
    
    public function getAddressLine2(): ?string
    {
        return $this->addressLine2;
    }
    
    public function setAddressLine2(?string $addressLine2): void
    {
        $this->addressLine2 = $addressLine2;
    }
    
    public function getUserAmount(): ?int
    {
        return $this->userAmount;
    }
    
    public function setUserAmount(?int $userAmount): void
    {
        $this->userAmount = $userAmount;
    }
    
    public function getPaymentType(): ?string
    {
        if (!$this->paymentType) {
            return null;
        }
        return $this->paymentType->getValue();
    }
    
    public function setPaymentType(?PaymentType $paymentType): void
    {
        $this->paymentType = $paymentType;
    }
    
    public function getPaymentCycle(): ?string
    {
        if (!$this->paymentCycle) {
            return null;
        }
        return $this->paymentCycle->getValue();
    }
    
    public function setPaymentCycle(?PaymentCycleType $paymentCycle): void
    {
        $this->paymentCycle = $paymentCycle;
    }
    
    /**
     * @Groups({"detail"})
     */
    public function getLogoUrl(): ?string
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
    
    public function getFiles(): array
    {
        return $this->files;
    }
    
    public function addFile(File $file): void
    {
        $this->files[] = $file->toArray();
    }
    
    public function updateFile(File $fileToUpdate): void
    {
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
    
    public function getCompany(): Company
    {
        return $this;
    }
    
    public function getAllFiles(): array
    {
        return $this->files;
    }
    
    public function getThumb(): ?string
    {
        return $this->getLogoUrl();
    }
    
    public function getThumbFile(): ?File
    {
        throw UnsupportedMethodException::forUnsupportedMethod('getThumbFile');
    }
}
