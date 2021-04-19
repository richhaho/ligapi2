<?php

declare(strict_types=1);


namespace App\Entity;


use App\Entity\Data\GridStateOwnerType;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GridStateRepository")
 */
class GridState implements CompanyAwareInterface
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="gridStates")
     */
    private Company $company;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="gridStates")
     */
    private ?User $user;
    
    /**
     * @ORM\Column(type="string")
     * @Groups({"list", "detail"})
     */
    private string $name;
    
    /**
     * @ORM\Column(type="string")
     * @Groups({"list", "detail"})
     */
    private string $gridType;
    
    /**
     * @ORM\Column(type="text")
     * @Groups({"list", "detail"})
     */
    private string $columnState;
    
    /**
     * @ORM\Column(type="text")
     * @Groups({"list", "detail"})
     */
    private string $sortState;
    
    /**
     * @ORM\Column(type="text")
     * @Groups({"list", "detail"})
     */
    private string $filterState;
    
    /**
     * @ORM\Column(type="integer")
     * @Groups({"list", "detail"})
     */
    private int $paginationState;
    
    /**
     * @ORM\Embedded(class="App\Entity\Data\GridStateOwnerType", columnPrefix="gridStateOwner_")
     */
    private GridStateOwnerType $owner;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isDefault;
    
    public function __construct(Company $company, string $gridType, GridStateOwnerType $owner, ?bool $isDefault, ?User $user)
    {
        $this->id = Uuid::uuid4()->toString();
        $this->createdAt = new DateTimeImmutable();
        $this->company = $company;
        $this->gridType = $gridType;
        $this->owner = $owner;
        $this->paginationState = 100;
        $this->user = $user;
        $this->sortState = "";
        $this->filterState = "";
        $this->columnState = "";
        $this->name = 'default';
        $this->isDefault = !!$isDefault;
    }
    
    public function getId(): string
    {
        return $this->id;
    }
    
    public function getCompany(): Company
    {
        return $this->company;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getGridType(): string
    {
        return $this->gridType;
    }
    
    public function getColumnState(): string
    {
        return $this->columnState;
    }
    
    public function getSortState(): string
    {
        return $this->sortState;
    }
    
    public function getFilterState(): string
    {
        return $this->filterState;
    }
    
    public function getPaginationState(): int
    {
        return $this->paginationState;
    }
    
    public function getOwner(): GridStateOwnerType
    {
        return $this->owner;
    }
    
    public function setName(string $name): void
    {
        $this->name = $name;
    }
    
    public function setColumnState(string $columnState): void
    {
        $this->columnState = $columnState;
    }
    
    public function setSortState(string $sortState): void
    {
        $this->sortState = $sortState;
    }
    
    public function setFilterState(string $filterState): void
    {
        $this->filterState = $filterState;
    }
    
    public function setPaginationState(int $paginationState): void
    {
        $this->paginationState = $paginationState;
    }
    
    public function isDefault(): bool
    {
        return $this->isDefault;
    }
    
    public function getUser(): User
    {
        return $this->user;
    }
}
