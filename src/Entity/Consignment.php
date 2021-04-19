<?php

declare(strict_types=1);


namespace App\Entity;


use App\Event\Log;
use App\Exceptions\Domain\InconsistentDataException;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ConsignmentRepository")
 * @ORM\Table(uniqueConstraints={
 *     @ORM\UniqueConstraint(name="idx_consignmentNumber", columns={"company_id", "consignment_number"})
 * })
 */
class Consignment implements LoggableInterface, CompanyAwareInterface
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=255)
     * @Groups({"list", "detail"})
     */
    private string $id;
    
    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"list", "detail"})
     */
    private DateTimeImmutable $createdAt;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="consignments")
     */
    private Company $company;
    
    /**
     * @ORM\Column(type="integer")
     * @Groups({"list", "detail"})
     */
    private int $consignmentNumber;
    
    /**
     * @ORM\Column(type="string")
     * @Groups({"list", "detail"})
     */
    private string $deliveryAddress;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ConsignmentItem", mappedBy="consignment")
     */
    private Collection $consignmentItems;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Project", inversedBy="consignments")
     * @Groups({"list", "detail"})
     * @Log()
     */
    private ?Project $project = null;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="consignments")
     * @Groups({"list", "detail"})
     * @Log()
     */
    private ?User $user = null;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Log()
     */
    private ?string $name = null;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"list", "detail"})
     * @Log()
     */
    private ?string $note = null;
    
    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Log()
     * @Groups({"list", "detail"})
     */
    private ?DateTimeImmutable $deliveryDate = null;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Location", inversedBy="consignments")
     * @Groups({"list", "detail"})
     * @Log()
     */
    private ?Location $location = null;
    
    public function __construct(Company $company, int $consignmentNumber, ?Project $project = null, ?User $user = null, ?string $name = null)
    {
        if ($project && ($user || $name)) {
            throw InconsistentDataException::forMultipleConsignmentIdentifiersSet($project->getName());
        }
        if ($user && ($project || $name)) {
            throw InconsistentDataException::forMultipleConsignmentIdentifiersSet($user->getFullName());
        }
        if ($name && ($project || $user)) {
            throw InconsistentDataException::forMultipleConsignmentIdentifiersSet($name);
        }
        if (!$name && !$project && !$user) {
            throw InconsistentDataException::forDataIsMissing('Please provide project, user or name. Consignment identifier');
        }
        
        $this->id = Uuid::uuid4()->toString();
        $this->createdAt = new DateTimeImmutable();
        $this->company = $company;
        $this->project = $project;
        $this->user = $user;
        $this->name = $name;
        $this->consignmentNumber = $consignmentNumber;
        $this->consignmentItems = new ArrayCollection();
        if ($project && $project->getCustomer()) {
            $this->deliveryAddress = $project->getCustomer()->getAddress();
        } else {
            $this->deliveryAddress = '';
        }
    }
    
    public function getLogData(): string
    {
        return $this->getName();
    }
    
    public function getNote(): ?string
    {
        return $this->note;
    }
    
    public function setName(string $name): void
    {
        if ($this->user || $this->project) {
            throw InconsistentDataException::forProjectNameCantBeChanged($this->id);
        }
        $this->name = $name;
    }
    
    public function getProject(): ?Project
    {
        return $this->project;
    }
    
    public function getCustomer(): ?Customer
    {
        if (!$this->getProject()) {
            return null;
        }
        return $this->getProject()->getCustomer();
    }
    
    public function getUser(): ?User
    {
        return $this->user;
    }
    
    public function setNote(?string $note): void
    {
        $this->note = $note;
    }
    
    /**
     * @Groups({"list", "detail"})
     */
    public function getName(): string
    {
        if ($this->name) {
            return $this->name;
        }
        if ($this->project) {
            return $this->project->getName();
        }
        if ($this->user) {
            return $this->user->getFullName();
        }
        throw InconsistentDataException::forDataIsMissing('Consignment name');
    }
    
    public function getId(): string
    {
        return $this->id;
    }
    
    public function getConsignmentItems()
    {
        return $this->consignmentItems;
    }
    
    /**
     * @Groups({"list", "detail"})
     */
    public function getOpenConsignmentItemsAmount(): int
    {
        return $this->consignmentItems->filter(function(ConsignmentItem $consignmentItem) {
            return $consignmentItem->getConsignmentItemStatus() === 'open';
        })->count();
    }
    
    /**
     * @Groups({"list", "detail"})
     */
    public function getAllConsignmentItemsAmount(): int
    {
        return count($this->consignmentItems);
    }
    
    /**
     * @Groups({"list", "detail"})
     */
    public function getConsignmentType(): string
    {
        if ($this->name) {
            return 'name';
        }
        if ($this->project) {
            return 'project';
        }
        if ($this->user) {
            return 'user';
        }
        throw InconsistentDataException::forDataIsMissing('Consignment name');
    }
    
    public function getLocation(): ?string
    {
        if (!$this->location) {
            return null;
        }
        return $this->location->getName();
    }
    
    public function getLocationObject(): ?Location
    {
        if (!$this->location) {
            return null;
        }
        return $this->location;
    }
    
    public function setLocation(?Location $location): void
    {
        $this->location = $location;
    }
    
    public function getCompany(): Company
    {
        return $this->company;
    }
    
    public function getConsignmentNumber(): int
    {
        return $this->consignmentNumber;
    }
    
    public function getDeliveryDate(): ?string
    {
        return $this->deliveryDate ? $this->deliveryDate->format('Y-m-d') : null;
    }
    
    public function setDeliveryDate(?DateTimeImmutable $deliveryDate): void
    {
        $this->deliveryDate = $deliveryDate;
    }
    
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
    
    public function getDeliveryAddress(): string
    {
        return $this->deliveryAddress;
    }
    
    public function setDeliveryAddress(string $deliveryAddress): void
    {
        $this->deliveryAddress = $deliveryAddress;
    }
    
    
}
