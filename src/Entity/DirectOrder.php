<?php

declare(strict_types=1);


namespace App\Entity;


use App\Entity\Data\AutoStatus;
use App\Event\Log;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DirectOrderRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="idx_directOrderNumber", columns={"company_id", "direct_order_number"})})
 */
class DirectOrder implements CompanyAwareInterface
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=255)
     * @Groups({"list", "detail", "directorderdetails", "orderdetails"})
     */
    private string $id;
    
    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"list", "directorderdetails"})
     */
    private DateTimeImmutable $createdAt;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="directOrders")
     */
    private Company $company;

    /**
    * @ORM\OneToMany(targetEntity="App\Entity\DirectOrderPosition", mappedBy="directOrder")
    * @Groups({"list", "directorderdetails"})
    */
    private Collection $directOrderPositions;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Supplier", inversedBy="directOrders")
     * @Groups({"list", "directorderdetails"})
     */
    private Supplier $mainSupplier;
    
    /**
     * @ORM\Column(type="integer")
     * @Log()
     * @Groups({"list", "directorderdetails", "orderedMaterials"})
     */
    private int $directOrderNumber;
    
    /**
     * @ORM\Embedded(class="App\Entity\Data\AutoStatus", columnPrefix="autoStatus_")
     * @Log()
     * @Groups({"list", "detail"})
     */
    private AutoStatus $status;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Log()
     * @Groups({"list", "detail"})
     */
    private ?string $statusDetails = null;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="directOrders")
     */
    private User $user;
    
    public function __construct(Supplier $mainSupplier, int $directOrderNumber, User $user)
    {
        $this->id = Uuid::uuid4()->toString();
        $this->createdAt = new DateTimeImmutable();
        $this->company = $user->getCompany();
        $this->user = $user;
        $this->status = AutoStatus::new();
        $this->mainSupplier = $mainSupplier;
        $this->directOrderNumber = $directOrderNumber;
        $this->directOrderPositions = new ArrayCollection();
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
    
    public function getDirectOrderPositions()
    {
        return $this->directOrderPositions;
    }
    
    public function getMainSupplier(): Supplier
    {
        return $this->mainSupplier;
    }
    
    public function getDirectOrderNumber(): int
    {
        return $this->directOrderNumber;
    }
    
    public function getStatus(): string
    {
        return $this->status->getValue();
    }
    
    public function getStatusDetails(): ?string
    {
        return $this->statusDetails;
    }
    
    public function setStatus(AutoStatus $status): void
    {
        $this->status = $status;
    }
    
    public function setStatusDetails(?string $statusDetails): void
    {
        $this->statusDetails = $statusDetails;
    }
    
    public function getUser(): User
    {
        return $this->user;
    }
    
}
