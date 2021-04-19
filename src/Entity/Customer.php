<?php


namespace App\Entity;

use App\Event\Log;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CustomerRepository")
 */
class Customer implements LoggableInterface, CompanyAwareInterface, DeleteUpdateAwareInterface
{
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="customers")
     */
    private Company $company;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private bool $deleted;
    
    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"list", "detail", "listStockChanges"})
     * @Log()
     */
    private string $name;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"list", "detail"})
     * @Log()
     */
    private ?string $street = null;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"list", "detail"})
     * @Log()
     */
    private ?string $zip = null;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"list", "detail"})
     * @Log()
     */
    private ?string $city = null;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"list", "detail"})
     * @Log()
     */
    private ?string $country = null;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"list", "detail"})
     * @Log()
     */
    private ?string $email = null;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"list", "detail"})
     * @Log()
     */
    private ?string $phone = null;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"list", "detail"})
     * @Log()
     */
    private ?string $firstName = null;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"list", "detail"})
     * @Log()
     */
    private ?string $lastName = null;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"list", "detail"})
     * @Log()
     */
    private ?string $shippingStreet = null;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"list", "detail"})
     * @Log()
     */
    private ?string $shippingZip = null;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"list", "detail"})
     * @Log()
     */
    private ?string $shippingCity = null;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"list", "detail"})
     * @Log()
     */
    private ?string $shippingCountry = null;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Project", mappedBy="customer")
     */
    private Collection $projects;
    
    public function __construct(string $name, Company $company)
    {
        $this->company = $company;
        $this->name = $name;
        $this->projects = new ArrayCollection();
        $this->id = Uuid::uuid4()->toString();
        $this->deleted = false;
        $this->createdAt = new DateTimeImmutable();
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

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function getZip(): ?string
    {
        return $this->zip;
    }
 
    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }
    
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getShippingStreet(): ?string
    {
        return $this->shippingStreet;
    }

    public function getShippingZip(): ?string
    {
        return $this->shippingZip;
    }

    public function getShippingCity(): ?string
    {
        return $this->shippingCity;
    }

    public function getShippingCountry(): ?string
    {
        return $this->shippingCountry;
    }
    
    
    public function setName(string $name): self
    {
        $this->name = $name;
        
        return $this;
    }
    
    public function setStreet(?string $street): self
    {
        $this->street = $street;
        
        return $this;
    }
    
    public function setZip(?string $zip): self
    {
        $this->zip = $zip;
        
        return $this;
    }
    
    public function setCity(?string $city): self
    {
        $this->city = $city;
        
        return $this;
    }
    
    public function setCountry(?string $country): self
    {
        $this->country = $country;
        
        return $this;
    }
    
    public function setEmail(?string $email): self
    {
        $this->email = $email;
        
        return $this;
    }
    
    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;
        
        return $this;
    }
    
    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;
        
        return $this;
    }
    
    public function setShippingStreet(?string $shippingStreet): self
    {
        $this->shippingStreet = $shippingStreet;
        
        return $this;
    }
    
    public function setShippingZip(?string $shippingZip): self
    {
        $this->shippingZip = $shippingZip;
        
        return $this;
    }
    
    public function setShippingCity(?string $shippingCity): self
    {
        $this->shippingCity = $shippingCity;
        
        return $this;
    }
    
    public function setShippingCountry(?string $shippingCountry): self
    {
        $this->shippingCountry = $shippingCountry;
        
        return $this;
    }
    
    public function getPhone(): ?string
    {
        return $this->phone;
    }
    
    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }
    
    public function getLogData(): string
    {
        return $this->getName();
    }
    
    public function getProjects()
    {
        return $this->projects;
    }
    
    public function getCompany(): Company
    {
        return $this->company;
    }
    
    public function getAddress(): string
    {
        $address = '';
        if ($this->getName()) {
            $address .= $this->getName() . PHP_EOL;
        }
        if ($this->getShippingStreet()) {
            $address .= $this->getShippingStreet() . PHP_EOL;
        }
        if ($this->getShippingZip()) {
            $address .= $this->getShippingZip() . ' ' . $this->getShippingCity();
        }
        return $address;
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
