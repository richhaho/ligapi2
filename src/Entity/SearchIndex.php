<?php

declare(strict_types=1);


namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;
use function Symfony\Component\String\u;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *     indexes = {
 *          @ORM\Index(
 *              columns = {"content", "entity_shortname"}, flags = {"fulltext"}
 *          )
 *      }
 * )
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="idx_itemId", columns={"company_id", "entity_id"})})
 */
class SearchIndex implements CompanyAwareInterface
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=255)
     */
    private string $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="searchIndexes")
     */
    private Company $company;
    
    /**
     * @ORM\Column(type="text")
     */
    private string $content;
    
    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"list"})
     */
    private string $entityId;
    
    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $entityShortname;
    
    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"list"})
     */
    private string $name;
    
    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private DateTimeImmutable $updatedAt;
    
    public function __construct(SearchableInterface $searchable)
    {
        $this->id = Uuid::uuid4()->toString();
        $this->company = $searchable->getCompany();
        $this->entityId = $searchable->getId();
        $shortname = u(get_class($searchable))->afterLast('\\')->lower()->toString();
        $this->entityShortname = $shortname;
        $this->name = $searchable->getName();
        $this->setContent($searchable->getSearchableText());
    }
    
    public function getEntityId(): string
    {
        return $this->entityId;
    }
    
    public function getEntityShortname(): string
    {
        return $this->entityShortname;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function setContent(string $content): void
    {
        $this->updatedAt = new DateTimeImmutable();
        $this->content = $content;
    }
    
    public function getCompany(): Company
    {
        return $this->company;
    }
    
}
