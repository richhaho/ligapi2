<?php

declare(strict_types=1);


namespace App\Entity;


use App\Entity\Data\AutoStatus;
use App\Event\Log;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DirectOrderPositionResultRepository")
 */
class DirectOrderPositionResult implements CompanyAwareInterface
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=255)
     * @Groups({"orderdetails", "directorderdetails"})
     */
    private string $id;
    
    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private DateTimeImmutable $createdAt;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="directOrderPositionResults")
     */
    private Company $company;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\DirectOrderPosition", inversedBy="directOrderPositionResults")
     */
    private DirectOrderPosition $directOrderPosition;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\OrderSource", inversedBy="directOrderPositionResults")
     * @Groups({"orderdetails", "directorderdetails"})
     */
    private OrderSource $orderSource;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"orderdetails", "directorderdetails"})
     */
    private ?string $availability = null;
    
    /**
     * @ORM\Embedded(class="App\Entity\Data\AutoStatus", columnPrefix="autoStatus_")
     * @Groups({"orderdetails", "directorderdetails"})
     * @Log()
     */
    private AutoStatus $autoStatus;
    
    public function __construct(OrderSource $orderSource, DirectOrderPosition $directOrderPosition)
    {
        $this->id = Uuid::uuid4()->toString();
        $this->createdAt = new DateTimeImmutable();
        $this->company = $orderSource->getCompany();
        $this->orderSource = $orderSource;
        $this->directOrderPosition = $directOrderPosition;
        $this->autoStatus = AutoStatus::new();
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
    
    public function getDirectOrderPosition(): DirectOrderPosition
    {
        return $this->directOrderPosition;
    }
    
    public function getOrderSource(): OrderSource
    {
        return $this->orderSource;
    }
    
    public function getAvailability(): ?string
    {
        return $this->availability;
    }
    
    public function getAutoStatus(): string
    {
        return $this->autoStatus->getValue();
    }
    
    public function setAvailability(?string $availability): void
    {
        $this->availability = $availability;
    }
    
    public function setAutoStatus(AutoStatus $autoStatus): void
    {
        $this->autoStatus = $autoStatus;
    }
}
