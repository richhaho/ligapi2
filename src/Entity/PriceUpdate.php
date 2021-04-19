<?php

declare(strict_types=1);


namespace App\Entity;


use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Money\Money;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PriceUpdateRepository")
 */
class PriceUpdate
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="orderSources")
     */
    private Company $company;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\OrderSource", fetch="EAGER", inversedBy="priceUpdates")
     */
    private OrderSource $orderSource;
    
    /**
     * @ORM\Column(type="float")
     */
    private float $amountPerPurchaseUnit;
    
    /**
     * @ORM\Column(type="money")
     */
    private Money $price;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private string $source;
    
    public function __construct(OrderSource $orderSource, string $source)
    {
        $this->id = Uuid::uuid4()->toString();
        $this->createdAt = new DateTimeImmutable();
        $this->company = $orderSource->getCompany();
        $this->orderSource = $orderSource;
        $this->amountPerPurchaseUnit = $orderSource->getAmountPerPurchaseUnit() ?? 1;
        $this->price = Money::EUR(intval(round(($orderSource->getPrice() ?? 0) * 100)));
        $this->source = $source;
    }
}
