<?php

declare(strict_types=1);


namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OwnerChangeRepository")
 */
class OwnerChange implements CompanyAwareInterface
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=255)
     * @Groups({"detail", "list"})
     */
    private string $id;
    
    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"detail", "list"})
     */
    private DateTimeImmutable $createdAt;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="ownerChanges")
     */
    private Company $company;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Tool", inversedBy="ownerChanges")
     * @Groups({"detail", "list"})
     */
    private ?Tool $tool = null;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Keyy", inversedBy="ownerChanges")
     * @Groups({"detail", "list"})
     */
    private ?Keyy $keyy = null;
    
    /**
     * @ORM\Column(type="string")
     * @Groups({"detail", "list"})
     */
    private string $newOwnerName;
    
    public function __construct(Company $company, string $newOwnerName, ?Tool $tool = null, ?Keyy $keyy = null)
    {
        $this->company = $company;
        $this->id = Uuid::uuid4()->toString();
        $this->createdAt = new DateTimeImmutable();
        $this->newOwnerName = $newOwnerName;
        $this->tool = $tool;
        $this->keyy = $keyy;
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
    
    public function getTool(): ?Tool
    {
        return $this->tool;
    }
    
    public function getKeyy(): ?Keyy
    {
        return $this->keyy;
    }
    
    public function getNewOwnerName(): string
    {
        return $this->newOwnerName;
    }
}
