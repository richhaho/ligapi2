<?php


namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * @ORM\Entity(repositoryClass="App\Repository\ConnectedSupplierRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="idx_name", columns={"name"})})
 */
class ConnectedSupplier
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=255)
     * @Groups({"list", "detail"})
     */
    private string $id;
    
    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"list", "detail"})
     */
    private string $name;
    
    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"list", "detail"})
     */
    private string $url;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Supplier", mappedBy="connectedSupplier")
     */
    private Collection $suppliers;
    
    public function __construct($name, $url)
    {
        $this->id = Uuid::uuid4()->toString();
        $this->name = $name;
        $this->url = $url;
        $this->suppliers = new ArrayCollection();
    }
    
    public function getId(): string
    {
        return $this->id;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getUrl(): string
    {
        return $this->url;
    }
    
}
