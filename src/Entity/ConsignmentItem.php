<?php

declare(strict_types=1);


namespace App\Entity;


use App\Entity\Data\ConsignmentItemStatus;
use App\Event\Log;
use App\Exceptions\Domain\InconsistentDataException;
use App\Exceptions\Domain\MissingDataException;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ConsignmentItemRepository")
 */
class ConsignmentItem implements LoggableInterface, CompanyAwareInterface
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="consignmentItems")
     */
    private Company $company;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Consignment", inversedBy="consignmentItems")
     * @Groups({"list", "detail"})
     * @Log()
     */
    private Consignment $consignment;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Material", inversedBy="consignmentItems")
     * @Log()
     */
    private ?Material $material = null;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Tool", inversedBy="consignmentItems")
     * @Log()
     */
    private ?Tool $tool = null;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Keyy", inversedBy="consignmentItems")
     * @Log()
     */
    private ?Keyy $keyy = null;
    
    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"list", "detail"})
     * @Log()
     */
    private ?float $amount = null;
    
    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"list", "detail"})
     * @Log()
     */
    private ?float $consignedAmount = null;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"list", "detail"})
     * @Log()
     */
    private ?string $manualName = null;
    
    /**
     * @ORM\Embedded(class="App\Entity\Data\ConsignmentItemStatus", columnPrefix="consignmentItemStatus_")
     * @Groups({"list", "detail"})
     * @Log()
     */
    private ConsignmentItemStatus $consignmentItemStatus;
    
    public function __construct(
        Company $company,
        Consignment $consignment,
        ?Material $material = null,
        ?Tool $tool = null,
        ?Keyy $keyy = null,
        ?string $manualName = null,
        ?float $amount = null
    )
    {
        $this->id = Uuid::uuid4()->toString();
        $this->createdAt = new DateTimeImmutable();
        $this->consignmentItemStatus = ConsignmentItemStatus::open();
        $this->company = $company;
        $this->consignment = $consignment;
        $this->material = $material;
        $this->tool = $tool;
        $this->keyy = $keyy;
        $this->manualName = $manualName;
        $this->amount = $amount;
        if ($amount) {
            $this->consignedAmount = 0;
        }
    }
    
    /**
     * @Groups({"list", "detail"})
     */
    public function getLinkedItem(): ?array
    {
        if ($this->material) {
            return [
                'name' => $this->material->getName(),
                'materialLocations' => $this->material->getMaterialLocations(),
                'linkedType' => 'material',
                'id' => $this->material->getId(),
                'itemNumber' => $this->material->getitemNumber(),
                'thumb' => $this->material->getThumb(),
                'location' => $this->material->getMainLocationLink() ? $this->material->getMainLocationLink()->getName() : '',
                'locationStock' => $this->material->getMainLocationLink() ? $this->material->getMainLocationLink()->getCurrentStock() : 0,
                'barcode' => $this->material->getBarcode()
            ];
        }
        if ($this->tool) {
            return [
                'name' => $this->tool->getName(),
                'owner' => $this->tool->getOwner(),
                'linkedType' => 'tool',
                'id' => $this->tool->getId(),
                'itemNumber' => $this->tool->getitemNumber(),
                'thumb' => $this->tool->getThumb(),
                'location' => $this->tool->getOwner(),
                'locationStock' => 1
            ];
        }
        if ($this->keyy) {
            return [
                'name' => $this->keyy->getName(),
                'owner' => $this->keyy->getOwner(),
                'linkedType' => 'keyy',
                'id' => $this->keyy->getId(),
                'itemNumber' => $this->keyy->getitemNumber(),
                'thumb' => $this->keyy->getThumb(),
                'location' => $this->keyy->getOwner(),
                'locationStock' => 1
            ];
        }
        throw InconsistentDataException::forDataIsMissing('ConsignmentItem linked item');
    }
    
    public function setAmount(?float $amount): void
    {
        if (!$this->consignedAmount) {
            $this->consignedAmount = 0;
        }
        if (!$amount) {
            $this->consignedAmount = null;
        }
        $this->amount = $amount;
    }
    
    public function setConsignmentItemStatus(ConsignmentItemStatus $consignmentItemStatus): void
    {
        $this->consignmentItemStatus = $consignmentItemStatus;
    }
    
    public function getLogData(): string
    {
        return $this->getLinkedItem()['name'];
    }
    
    public function getConsignment(): Consignment
    {
        return $this->consignment;
    }
    
    public function getAmount(): ?float
    {
        return $this->amount;
    }
    
    public function getConsignmentItemStatus(): string
    {
        return $this->consignmentItemStatus->getValue();
    }
    
    public function getId(): string
    {
        return $this->id;
    }
    
    public function getConsignedAmount()
    {
        return $this->consignedAmount;
    }
    
    public function setConsignedAmount($consignedAmount): void
    {
        $this->consignedAmount = $consignedAmount;
    }
    
    public function getMaterial(): ?Material
    {
        return $this->material;
    }
    
    public function getTool(): ?Tool
    {
        return $this->tool;
    }
    
    public function getKeyy(): ?Keyy
    {
        return $this->keyy;
    }
    
    public function getConsignmentItemSubject(): PermissionAwareInterface
    {
        if ($this->material) {
            return $this->material;
        }
        if ($this->tool) {
            return $this->tool;
        }
        if ($this->keyy) {
            return $this->keyy;
        }
        
        throw MissingDataException::forMissingData('Consignment Item Subject is missing');
    }
    
    public function getCompany(): Company
    {
        return $this->company;
    }
    
    public function getManualName(): ?string
    {
        return $this->manualName;
    }
}
