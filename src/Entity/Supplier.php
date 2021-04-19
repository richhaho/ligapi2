<?php


namespace App\Entity;

use App\Event\Log;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * @ORM\Entity(repositoryClass="App\Repository\SupplierRepository")
 */
class Supplier implements LoggableInterface, CompanyAwareInterface, DeleteUpdateAwareInterface
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=255)
     * @Groups({"list", "detail", "orderdetails", "shoppingCart", "directorderdetails"})
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="suppliers")
     */
    private Company $company;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private bool $deleted;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\OrderSource", mappedBy="supplier")
     */
    private Collection $orderSources;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MaterialOrder", mappedBy="supplier")
     */
    private Collection $materialOrders;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\DirectOrder", mappedBy="mainSupplier")
     */
    private Collection $directOrders;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"list", "detail", "orderdetails", "shoppingCart", "orderedMaterials", "directorderdetails"})
     * @Log()
     */
    private string $name;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"detail", "list"})
     * @Log()
     */
    private ?string $customerNumber = null;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"detail", "list"})
     * @Log()
     */
    private ?string $webShopLogin = null;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $webShopPassword = null;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"detail", "list"})
     * @Log()
     */
    private ?string $street = null;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"detail", "list"})
     * @Log()
     */
    private ?string $zipCode = null;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"detail", "list"})
     * @Log()
     */
    private ?string $city = null;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"detail", "list"})
     * @Log()
     */
    private ?string $country = null;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"detail", "list"})
     * @Log()
     */
    private ?string $email = null;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"detail", "list"})
     * @Log()
     */
    private ?string $phone = null;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"detail", "list"})
     * @Log()
     */
    private ?string $fax = null;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"detail", "list"})
     * @Log()
     */
    private ?string $responsiblePerson = null;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"detail", "list"})
     * @Log()
     */
    private ?string $emailSalutation = null;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ConnectedSupplier", inversedBy="suppliers")
     * @Groups({"detail", "list"})
     */
    private ?ConnectedSupplier $connectedSupplier = null;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Material", mappedBy="autoSupplier")
     */
    private Collection $autoMaterials;
    
    public function __construct($name, $company)
    {
        $this->id = Uuid::uuid4()->toString();
        $this->company = $company;
        $this->createdAt = new DateTimeImmutable();
        $this->orderSources = new ArrayCollection();
        $this->name = $name;
        $this->autoMaterials = new ArrayCollection();
        $this->materialOrders = new ArrayCollection();
        $this->directOrders = new ArrayCollection();
        $this->setDeleted(false);
    }
    
    public function getId(): string
    {
        return $this->id;
    }
    
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
    
    public function getOrderSources(): Collection
    {
        return $this->orderSources;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getCustomerNumber(): ?string
    {
        return $this->customerNumber;
    }
    
    public function getWebShopLogin(): ?string
    {
        return $this->webShopLogin;
    }
    
    public function getEncryptedwebShopPassword(): ?string
    {
        return $this->webShopPassword;
    }
    
    public function getStreet(): ?string
    {
        return $this->street;
    }
    
    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }
    
    public function getCity(): ?string
    {
        return $this->city;
    }
    
    public function getEmail(): ?string
    {
        return $this->email;
    }
    
    public function getPhone(): ?string
    {
        return $this->phone;
    }
    
    public function getFax(): ?string
    {
        return $this->fax;
    }
    
    public function hasConnectedSupplier(): bool
    {
        return !!$this->connectedSupplier;
    }
    
    public function getConnectedSupplier(): ?ConnectedSupplier
    {
        return $this->connectedSupplier;
    }
    
    public function getResponsiblePerson(): ?string
    {
        return $this->responsiblePerson;
    }
    
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }
    
    public function setCustomerNumber(?string $customerNumber): self
    {
        $this->customerNumber = $customerNumber;
        return $this;
    }
    
    public function setWebShopLogin(?string $webShopLogin): self
    {
        $this->webShopLogin = $webShopLogin;
        return $this;
    }
    
    public function setwebShopPassword(?string $webShopPassword): self
    {
        $this->webShopPassword = $webShopPassword;
        return $this;
    }
    
    public function setStreet(?string $street): self
    {
        $this->street = $street;
        return $this;
    }
    
    public function setZipCode(?string $zipCode): self
    {
        $this->zipCode = $zipCode;
        return $this;
    }
    
    public function setCity(?string $city): self
    {
        $this->city = $city;
        return $this;
    }
    
    public function setEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }
    
    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }
    
    public function setFax(?string $fax): self
    {
        $this->fax = $fax;
        return $this;
    }
    
    public function setConnectedSupplier(?ConnectedSupplier $connectedSupplier): self
    {
        $this->connectedSupplier = $connectedSupplier;
        return $this;
    }
    
    public function setResponsiblePerson(?string $responsiblePerson): self
    {
        $this->responsiblePerson = $responsiblePerson;
        return $this;
    }
    
    /**
     * @Groups({"detail"})
     */
    public function getConnectedSupplierId(): ?string
    {
        if ($this->connectedSupplier) {
            return $this->connectedSupplier->getId();
        }
        return null;
    }
    
    /**
     * @Groups({"detail"})
     */
    public function getConnectedSupplierName(): ?string
    {
        if ($this->connectedSupplier) {
            return $this->connectedSupplier->getName();
        }
        return null;
    }
    
    public function getEmailSalutation(): ?string
    {
        return $this->emailSalutation;
    }
    
    public function setEmailSalutation(?string $emailSalutation): void
    {
        $this->emailSalutation = $emailSalutation;
    }
    
    public function __toString(): ?string
    {
        return $this->getId();
    }
    
    public function getLogData(): string
    {
        return $this->getName();
    }
    
    public function getAddressArray(): array
    {
        return [
            $this->name,
            $this->responsiblePerson,
            $this->street,
            $this->zipCode . ' ' . $this->city,
        ];
    }
    
    public function getMaterialOrders()
    {
        return $this->materialOrders;
    }
    
    public function getAutoMaterials()
    {
        return $this->autoMaterials;
    }
    
    public function getMaterials(): array
    {
        $orderSources = $this->orderSources;
        $materials = [];
        /** @var OrderSource $orderSource */
        foreach ($orderSources as $orderSource) {
            $materials[] = $orderSource->getMaterial();
        }
        return $materials;
    }
    
    public function getCompany(): Company
    {
        return $this->company;
    }
    
    public function getCountry(): ?string
    {
        return $this->country;
    }
    
    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }
    
    public function getDirectOrders()
    {
        return $this->directOrders;
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
