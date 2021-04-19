<?php


namespace App\Entity;

use App\Exceptions\Domain\InvalidArgumentException;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LocationRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="idx_name", columns={"company_id", "name"})})
 */
class Location implements LoggableInterface, CompanyAwareInterface
{
    /**
    * @ORM\Id()
    * @ORM\Column(type="string", length=255)
    */
    private string $id;
    
    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private DateTimeImmutable $createdAt;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="locations")
     */
    private Company $company;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"list", "detail", "stocktaking"})
     */
    private ?string $name;
    
    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User", inversedBy="location")
     */
    private ?User $user;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Keyy", mappedBy="home")
     */
    private Collection $keyyHomes;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Keyy", mappedBy="owner")
     */
    private Collection $keyyOwners;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Tool", mappedBy="home")
     */
    private Collection $toolHomes;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Tool", mappedBy="owner")
     */
    private Collection $toolOwners;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Consignment", mappedBy="location")
     */
    private Collection $consignments;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MaterialLocation", mappedBy="location")
     * @Groups({"stocktaking"})
     */
    private Collection $materialLocations;
    
    private function __construct(Company $company, string $name = null, User $user = null)
    {
        $this->company = $company;
        $this->name = $name;
        $this->user = $user;
        $this->id = Uuid::uuid4()->toString();
        $this->createdAt = new DateTimeImmutable();
        $this->materialLocations = new ArrayCollection();
        $this->keyyHomes = new ArrayCollection();
        $this->keyyOwners = new ArrayCollection();
        $this->toolHomes = new ArrayCollection();
        $this->toolOwners = new ArrayCollection();
        $this->consignments = new ArrayCollection();
    }
    
    public static function forUser(User $user): self
    {
        return new self($user->getCompany(), null, $user);
    }
    
    public static function forCompany(string $locationName, Company $company): self
    {
        return new self($company, $locationName);
    }
 
    public function getId(): string
    {
        return $this->id;
    }
  
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getMaterialLocations(): Collection
    {
        return $this->materialLocations;
    }
    
    public function getCompany(): Company
    {
        return $this->company;
    }
    
    public function getName(): ?string
    {
        if ($this->isPersonal()) {
            return $this->user->getFullName();
        }
        
        return $this->name;
    }
    
    public function getUser(): ?User
    {
        return $this->user;
    }
    
    public function isPersonal(): bool
    {
        return null !== $this->user;
    }
    
    public function assignName(string $name): self
    {
        if ($this->isPersonal()) {
            throw InvalidArgumentException::forUnsupportedLocationType($this->getId(), $name);
        }
        
        $this->name = $name;
        
        return $this;
    }
    
    public function getLogData(): string
    {
        return $this->getName();
    }
    
    public function resetUser(): void
    {
        $this->user = null;
    }
    
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
