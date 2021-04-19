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
 * @ORM\Entity(repositoryClass="App\Repository\PermissionGroupRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="idx_name", columns={"company_id", "name"})})
 */
class PermissionGroup implements LoggableInterface, CompanyAwareInterface
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=255)
     * @Groups({"list", "detail"})
     */
    private string $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="permissionGroups")
     */
    private Company $company;
    
    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private DateTimeImmutable $createdAt;
    
    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"list", "detail"})
     */
    private string $name;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Keyy", mappedBy="permissionGroup")
     */
    private Collection $keyys;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Material", mappedBy="permissionGroup")
     */
    private Collection $materials;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Tool", mappedBy="permissionGroup")
     */
    private Collection $tools;
    
    public function __construct(string $name, Company $company)
    {
        $this->company = $company;
        $this->name = $name;
        $this->id = Uuid::uuid4()->toString();
        $this->createdAt = new DateTimeImmutable();
        $this->keyys = new ArrayCollection();
        $this->materials = new ArrayCollection();
        $this->tools = new ArrayCollection();
    }
    
    public function getId(): string
    {
        return $this->id;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function setName(string $name): void
    {
        $this->name = $name;
    }
    
    public function getLogData(): string
    {
        return $this->getName();
    }
    
    public function getKeyys()
    {
        return $this->keyys;
    }
    
    public function getMaterials()
    {
        return $this->materials;
    }
    
    public function getTools()
    {
        return $this->tools;
    }
    
    public function getCompany(): Company
    {
        return $this->company;
    }
}
