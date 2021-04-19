<?php

declare(strict_types=1);


namespace App\Entity;


use App\Event\Log;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MaterialOrderPositionRepository")
 */
class MaterialOrderPosition implements LoggableInterface, CompanyAwareInterface
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=255)
     * @Groups({"orderdetails", "list", "orderedMaterials", "directorderdetails"})
     */
    private string $id;
    
    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private DateTimeImmutable $createdAt;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="materialOrderPositions")
     */
    private Company $company;
    
    /**
     * @ORM\Column(type="float")
     * @Groups({"orderdetails", "list","orderedMaterials", "directorderdetails"})
     * @Log()
     */
    private float $amount;
    
    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"orderedMaterials"})
     * @Log()
     */
    private ?float $price;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"orderdetails", "list", "directorderdetails"})
     * @Log()
     */
    private ?string $statusMessage;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Log()
     * @Groups({"orderdetails", "orderedMaterials", "directorderdetails"})
     */
    private ?string $orderNote;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\OrderSource", inversedBy="materialOrderPositions")
     * @Groups({"orderdetails", "orderedMaterials"})
     */
    private OrderSource $orderSource;
    
    /**
     * @ORM\OneToOne(targetEntity="App\Entity\DirectOrderPosition", mappedBy="materialOrderPosition")
     */
    private ?DirectOrderPosition $directOrderPosition = null;
    
    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Material", mappedBy="lastMaterialOrderPosition")
     */
    private Material $material;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MaterialOrder", inversedBy="materialOrderPositions")
     * @Groups({"orderedMaterials", "directorderdetails"})
     */
    private MaterialOrder $materialOrder;
    
    public function __construct(
        Company $company,
        float $amount,
        OrderSource $orderSource,
        MaterialOrder $materialOrder,
        ?float $price = null,
        ?string $statusMessage = null,
        ?string $orderNote = null
    )
    {
        $this->id = Uuid::uuid4()->toString();
        $this->createdAt = new DateTimeImmutable();
        $this->company = $company;
        $this->amount = $amount;
        $this->statusMessage = $statusMessage;
        $this->orderSource = $orderSource;
        $this->materialOrder = $materialOrder;
        $this->price = $price;
        $this->orderNote = $orderNote;
        $this->material = $orderSource->getMaterial();
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
    
    public function getAmount(): float
    {
        return $this->amount;
    }
    
    public function getStatusMessage(): ?string
    {
        return $this->statusMessage;
    }
    
    public function getOrderSource(): OrderSource
    {
        return $this->orderSource;
    }
    
    
    public function getLogData(): string
    {
        return $this->id;
    }
    
    public function setStatusMessage(?string $statusMessage): void
    {
        $this->statusMessage = $statusMessage;
    }
    
    public function getMaterial(): Material
    {
        return $this->orderSource->getMaterial();
    }
    
    public function getMaterialOrder(): MaterialOrder
    {
        return $this->materialOrder;
    }
    
    public function getPrice(): ?float
    {
        return $this->price;
    }
    
    public function getOrderNote(): ?string
    {
        return $this->orderNote;
    }
    
    public function getDirectOrderPosition(): ?DirectOrderPosition
    {
        return $this->directOrderPosition;
    }
    
    public function setDirectOrderPosition(?DirectOrderPosition $directOrderPosition): void
    {
        $this->directOrderPosition = $directOrderPosition;
    }
}
