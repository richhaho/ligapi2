<?php


namespace App\Entity;

use App\Entity\Data\ItemGroupType;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ItemGroupRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="idx_name", columns={"company_id", "name", "itemGroupType_value"})})
 */
class ItemGroup implements LoggerAwareInterface, CompanyAwareInterface
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=255)
     * @Groups({"list", "detail"})
     */
    private string $id;
    
    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private DateTimeImmutable $createdAt;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="itemGroups")
     */
    private Company $company;
    
    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"list", "detail"})
     */
    private string $name;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Tool", mappedBy="itemGroup")
     */
    private Collection $tools;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Material", mappedBy="itemGroup")
     */
    private Collection $materials;
    
    /**
     * @ORM\Embedded(class="App\Entity\Data\ItemGroupType", columnPrefix="itemGroupType_")
     * @Groups({"list", "detail"})
     */
    private ItemGroupType $itemGroupType;
    
    public function __construct(string $name, ItemGroupType $itemGroupType, Company $company)
    {
        $this->company = $company;
        $this->name = $name;
        $this->id = Uuid::uuid4()->toString();
        $this->createdAt = new DateTimeImmutable();
        $this->tools = new ArrayCollection();
        $this->materials = new ArrayCollection();
        $this->itemGroupType = $itemGroupType;
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
    
    
    public function setName(string $name): self
    {
        $this->name = $name;
        
        return $this;
    }
    
    public function setLogger(LoggerInterface $logger): string
    {
        return $this->getName();
    }
    
    public function getTools()
    {
        return $this->tools;
    }
    
    public function getMaterials()
    {
        return $this->materials;
    }
    
    public function getItemGroupType(): string
    {
        return $this->itemGroupType->getValue();
    }
    
    public function __toString(): ?string
    {
        return $this->getId();
    }
    
    public function getCompany(): Company
    {
        return $this->company;
    }
}
