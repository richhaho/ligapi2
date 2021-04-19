<?php

declare(strict_types=1);


namespace App\Entity;


use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DirectOrderPositionRepository")
 */
class DirectOrderPosition implements CompanyAwareInterface
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=255)
     * @Groups({"list", "directorderdetails"})
     */
    private string $id;
    
    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private DateTimeImmutable $createdAt;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="directOrderPositions")
     */
    private Company $company;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\DirectOrder", inversedBy="directOrderPositions")
     */
    private DirectOrder $directOrder;
    
    /**
     * @ORM\Column(type="string")
     * @Groups({"directorderdetails"})
     */
    private string $orderNumber;
    
    /**
     * @ORM\Column(type="integer")
     * @Groups({"directorderdetails"})
     */
    private int $amount;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\DirectOrderPositionResult", mappedBy="directOrderPosition")
     * @Groups({"directorderdetails"})
     */
    private Collection $directOrderPositionResults;
    
    /**
     * @ORM\OneToOne(targetEntity="App\Entity\MaterialOrderPosition", inversedBy="directOrderPosition")
     * @Groups({"directorderdetails"})
     */
    private ?MaterialOrderPosition $materialOrderPosition = null;
    
    public function __construct(
        DirectOrder $directOrder,
        string $orderNumber,
        int $amount
    )
    {
        $this->id = Uuid::uuid4()->toString();
        $this->createdAt = new DateTimeImmutable();
        $this->company = $directOrder->getCompany();
        $this->directOrder = $directOrder;
        $this->orderNumber = $orderNumber;
        $this->amount = $amount;
        $this->directOrderPositionResults = new ArrayCollection();
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
    
    public function getDirectOrder(): DirectOrder
    {
        return $this->directOrder;
    }
    
    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }
    
    public function getAmount(): int
    {
        return $this->amount;
    }
    
    public function getDirectOrderPositionResults()
    {
        return $this->directOrderPositionResults;
    }
    
    /**
     * @Groups({"directorderdetails"})
     */
    public function getMaterial(): ?Material
    {
        /** @var DirectOrderPositionResult $firstDirectOrderPositionResult */
        $firstDirectOrderPositionResult = $this->directOrderPositionResults->first();
        
        if ($firstDirectOrderPositionResult) {
            return $firstDirectOrderPositionResult->getOrderSource()->getMaterial();
        }
        
        return null;
    }
    
    public function getMaterialOrderPosition(): ?MaterialOrderPosition
    {
        return $this->materialOrderPosition;
    }
    
    public function setMaterialOrderPosition(MaterialOrderPosition $materialOrderPosition): void
    {
        $this->materialOrderPosition = $materialOrderPosition;
    }
    
}
