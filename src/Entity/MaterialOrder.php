<?php

declare(strict_types=1);


namespace App\Entity;


use App\Entity\Data\MaterialOrderStatus;
use App\Entity\Data\MaterialOrderType;
use App\Event\Log;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * @ORM\Entity(repositoryClass="App\Repository\MaterialOrderRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="idx_materialOrderNumber", columns={"company_id", "material_order_number"})})
 */
class MaterialOrder implements LoggableInterface, CompanyAwareInterface
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=255)
     * @Groups({"orderdetails", "list", "directorderdetails"})
     */
    private string $id;
    
    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"orderdetails", "list", "directorderdetails"})
     */
    private DateTimeImmutable $createdAt;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="materialOrders")
     */
    private Company $company;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MaterialOrderPosition", mappedBy="materialOrder")
     * @Groups({"orderdetails"})
     */
    private Collection $materialOrderPositions;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Supplier", fetch="EAGER", inversedBy="materialOrders")
     * @Groups({"list", "orderdetails"})
     */
    private Supplier $supplier;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Log()
     * @Groups({"orderdetails", "orderedMaterials"})
     */
    private ?string $deliveryNote;
    
    /**
     * @ORM\Column(type="integer")
     * @Log()
     * @Groups({"list", "orderdetails", "orderedMaterials", "directorderdetails"})
     */
    private int $materialOrderNumber;
    
    /**
     * @ORM\Embedded(class="App\Entity\Data\MaterialOrderStatus", columnPrefix="materialOrderStatus_")
     * @Log()
     * @Groups({"list", "orderdetails", "directorderdetails"})
     */
    private MaterialOrderStatus $materialOrderStatus;
    
    /**
     * @ORM\Embedded(class="App\Entity\Data\MaterialOrderType", columnPrefix="materialOrderType_")
     * @Groups({"orderdetails", "list", "orderedMaterials", "directorderdetails"})
     * @Log()
     */
    private MaterialOrderType $materialOrderType;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"orderdetails", "orderedMaterials", "directorderdetails"})
     */
    private ?string $fileLink = null;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Log()
     * @Groups({"list", "orderdetails", "orderedMaterials", "directorderdetails"})
     */
    private ?string $consignmentNumber = null;
    
    public function __construct(
        MaterialOrderType $materialOrderType,
        Supplier $supplier,
        Company $company,
        int $materialOrderNumber,
        ?string $deliveryNote = null
    )
    {
        $this->id = Uuid::uuid4()->toString();
        $this->createdAt = new DateTimeImmutable();
        $this->materialOrderStatus = MaterialOrderStatus::new();
        $this->materialOrderPositions = new ArrayCollection();
        $this->company = $company;
        $this->supplier = $supplier;
        $this->materialOrderType = $materialOrderType;
        $this->deliveryNote = $deliveryNote;
        $this->materialOrderNumber = $materialOrderNumber;
    }
    
    /**
     * @return MaterialOrderPosition[]
     */
    public function getMaterialOrderPositions(): Collection
    {
        return $this->materialOrderPositions;
    }
    
    public function addMaterialOrderPosition(MaterialOrderPosition $materialOrderPosition)
    {
        $this->materialOrderPositions->add($materialOrderPosition);
    }
    
    public function getSupplier(): Supplier
    {
        return $this->supplier;
    }
    
    public function getDeliveryNote(): ?string
    {
        return $this->deliveryNote;
    }
    
    public function getMaterialOrderStatus(): string
    {
        return $this->materialOrderStatus->getValue();
    }
    
    public function setMaterialOrderStatus(MaterialOrderStatus $materialOrderStatus): void
    {
        $this->materialOrderStatus = $materialOrderStatus;
    }
    
    public function getId(): string
    {
        return $this->id;
    }
    
    public function getLogData(): string
    {
        return $this->getId();
    }
    
    public function getMaterialOrderType(): string
    {
        return $this->materialOrderType->getValue();
    }
    
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
    
    public function getFileLink(): ?string
    {
        return $this->fileLink;
    }
    
    public function getCompany(): Company
    {
        return $this->company;
    }
    
    public function setFileLink(?string $fileLink): void
    {
        $this->fileLink = $fileLink;
    }
    
    /**
     * @Groups({"list"})
     */
    public function getMaterialOrderPositionsCount(): int
    {
        return $this->materialOrderPositions->count();
    }
    
    public function getMaterialOrderNumber(): int
    {
        return $this->materialOrderNumber;
    }
    
    public function getConsignmentNumber(): ?string
    {
        return $this->consignmentNumber;
    }
    
    public function setConsignmentNumber(?string $consignmentNumber): void
    {
        $this->consignmentNumber = $consignmentNumber;
    }
}
