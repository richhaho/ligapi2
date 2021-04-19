<?php

declare(strict_types=1);


namespace App\Entity;


use App\Entity\Data\ChangeAction;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ChangeLogRepository")
 */
class ChangeLog
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=255)
     */
    private string $id;
    
    /**
     * @ORM\Column(type="string", length=50)
     */
    private string $companyId;
    
    /**
     * @ORM\Column(type="string", length=50)
     */
    private string $userId;
    
    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private DateTimeImmutable $createdAt;
    
    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $objectClass;
    
    /**
     * @ORM\Embedded(class="App\Entity\Data\ChangeAction", columnPrefix="changeAction_")
     */
    private ChangeAction $action;
    
    /**
     * @ORM\Column(type="string", length=40)
     */
    private string $objectId;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $property;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $newValue;
    
    public function __construct(
        string $companyId,
        string $userId,
        string $objectClass,
        ChangeAction $action,
        string $objectId,
        ?string $property = null,
        ?string $newValue = null
    )
    {
        $this->id = Uuid::uuid4()->toString();
        $this->createdAt = new DateTimeImmutable();
        $this->companyId = $companyId;
        $this->userId = $userId;
        $this->objectClass = $objectClass;
        $this->action = $action;
        $this->objectId = $objectId;
        $this->property = $property;
        $this->newValue = $newValue;
    }
    
    public function getId(): string
    {
        return $this->id;
    }
    
    public function getUserId(): string
    {
        return $this->userId;
    }
    
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
    
    public function getObjectClass(): string
    {
        return $this->objectClass;
    }
    
    public function getAction(): ChangeAction
    {
        return $this->action;
    }
    
    public function getObjectId(): string
    {
        return $this->objectId;
    }
    
    public function getProperty(): ?string
    {
        return $this->property;
    }
    
    public function getNewValue(): ?string
    {
        return $this->newValue;
    }
}
