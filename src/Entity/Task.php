<?php

declare(strict_types=1);


namespace App\Entity;


use App\Entity\Data\File;
use App\Entity\Data\TaskStatus;
use App\Event\Log;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TaskRepository")
 */
class Task implements CompanyAwareInterface, FileAwareInterface, LoggableInterface
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="tasks")
     */
    private Company $company;
    
    /**
     * @ORM\Column(type="string")
     * @Groups({"detail", "list"})
     * @Log()
     */
    private string $topic;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"list", "detail"})
     * @Log()
     */
    private ?string $details;
    
    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     * @Groups({"detail", "list"})
     * @Log()
     */
    private ?DateTimeImmutable $startDate;
    
    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     * @Groups({"detail", "list"})
     * @Log()
     */
    private ?DateTimeImmutable $dueDate;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"detail"})
     * @Log()
     */
    private ?int $repeatAfterDays = null;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"detail"})
     * @Log()
     */
    private ?int $priority = null;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="tasks")
     * @Log()
     * @Groups({"list", "detail"})
     */
    private User $responsible;
    
    /**
     * @ORM\Embedded(class="App\Entity\Data\TaskStatus", columnPrefix="taskStatus_")
     * @Log()
     * @Groups({"list", "detail"})
     */
    private TaskStatus $taskStatus;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Tool", fetch="EAGER", inversedBy="tasks")
     * @Groups({"update"})
     */
    private ?Tool $tool;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Material", fetch="EAGER", inversedBy="tasks")
     * @Groups({"update"})
     */
    private ?Material $material;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Keyy", fetch="EAGER", inversedBy="tasks")
     * @Groups({"update"})
     */
    private ?Keyy $keyy;
    
    /**
     * @ORM\Column(type="json")
     * @Groups({"detail"})
     */
    private array $files;
    
    public function __construct(Company $company, string $topic, User $responsible, ?Material $material = null, ?Tool $tool = null, ?Keyy $keyy = null)
    {
        $this->id = Uuid::uuid4()->toString();
        $this->company = $company;
        $this->createdAt = new DateTimeImmutable();
        $this->topic = $topic;
        $this->material = $material;
        $this->tool = $tool;
        $this->keyy = $keyy;
        $this->files = [];
        $this->responsible = $responsible;
        $this->taskStatus = TaskStatus::open();
    }
    
    public function getFiles(): array
    {
        $files = [];
        foreach ( $this->files as $file ) {
            if ( $file['docType'] !== 'thumb' ) {
                $files[] = $file;
            }
        }
        return $files;
    }
    
    public function addFile(File $fileToAdd): void
    {
        $this->files[] = $fileToAdd->toArray();
    }
    
    public function updateFile(File $fileToUpdate): void
    {
        /** @var File $file */
        foreach ($this->files as $index => $fileArray) {
            $file = File::fromArray($fileArray);
            if ($file->getRelativePath() === $fileToUpdate->getRelativePath()) {
                $this->files[$index] = $fileToUpdate->toArray();
            }
        }
    }
    
    public function removeFile(File $fileToRemove): void
    {
        foreach ($this->files as $index => $fileArray) {
            $file = File::fromArray($fileArray);
            if ($file->getRelativePath() === $fileToRemove->getRelativePath()) {
                unset($this->files[$index]);
            }
        }
        $this->files = array_values($this->files);
    }
    
    /**
     * @Groups({"list", "detail"})
     */
    public function getThumb(): ?string
    {
        /** @var File $file */
        foreach ($this->files as $index => $fileArray) {
            $file = File::fromArray($fileArray);
            if ($file->getDocType() === 'thumb') {
                return $file->getRelativePath();
            }
        }
        return null;
    }
    
    /**
     * @Groups({"list", "detail"})
     */
    public function getThumbFile(): ?File
    {
        /** @var File $file */
        foreach ($this->files as $index => $fileArray) {
            $file = File::fromArray($fileArray);
            if ($file->getDocType() === 'thumb') {
                return $file;
            }
        }
        return null;
    }
    
    public function getCompany(): Company
    {
        return $this->company;
    }
    
    public function getId(): string
    {
        return $this->id;
    }
    
    public function getTopic(): string
    {
        return $this->topic;
    }
    
    public function setTopic(string $topic): void
    {
        $this->topic = $topic;
    }
    
    public function getDetails(): ?string
    {
        return $this->details;
    }
    
    public function setDetails(?string $details): void
    {
        $this->details = $details;
    }
    
    public function getStartDate(): ?string
    {
        if ($this->startDate) {
            return $this->startDate->format('Y-m-d');
        }
        return null;
    }
    
    public function getStartDateAsDateTime(): ?DateTimeImmutable
    {
        return $this->startDate;
    }
    
    public function setStartDate(?DateTimeImmutable $startDate): void
    {
        $this->startDate = $startDate;
    }
    
    public function setStartDateFromString(?string $startDateString): self
    {
        if ($startDateString) {
            if (str_contains($startDateString, '.')) {
                $this->startDate = DateTimeImmutable::createFromFormat('d.m.Y', $startDateString);
            } else {
                $this->startDate = new DateTimeImmutable($startDateString);
            }
        } else {
            $this->startDate = null;
        }
        return $this;
    }
    
    public function getDueDate(): ?string
    {
        if ($this->dueDate) {
            return $this->dueDate->format('Y-m-d');
        }
        return null;
    }
    
    public function setDueDate(?DateTimeImmutable $dueDate): void
    {
        $this->dueDate = $dueDate;
    }
    
    public function setDueDateFromString(?string $dueDateString): self
    {
        if ($dueDateString) {
            if (str_contains($dueDateString, '.')) {
                $this->dueDate = DateTimeImmutable::createFromFormat('d.m.Y', $dueDateString);
            } else {
                $this->dueDate = new DateTimeImmutable($dueDateString);
            }
        } else {
            $this->dueDate = null;
        }
        return $this;
    }
    
    public function getRepeatAfterDays(): ?int
    {
        return $this->repeatAfterDays;
    }
    
    public function setRepeatAfterDays(?int $repeatAfterDays): void
    {
        $this->repeatAfterDays = $repeatAfterDays;
    }
    
    public function getPriority(): ?int
    {
        return $this->priority;
    }
    
    public function setPriority(?int $priority): void
    {
        $this->priority = $priority;
    }
    
    public function getResponsible(): string
    {
        return $this->responsible->getFullName();
    }
    
    public function setResponsible(User $responsible): void
    {
        $this->responsible = $responsible;
    }
    
    public function getTaskStatus(): string
    {
        return $this->taskStatus->getValue();
    }
    
    public function setTaskStatus(TaskStatus $taskStatus): void
    {
        $this->taskStatus = $taskStatus;
    }
    
    public function getTool(): ?Tool
    {
        return $this->tool;
    }
    
    public function getMaterial(): ?Material
    {
        return $this->material;
    }
    
    public function getKeyy(): ?Keyy
    {
        return $this->keyy;
    }
    
    /**
     * @Groups({"list", "detail"})
     */
    public function getLinkedItem(): ?array
    {
        if ($this->material) {
            return [
                'name' => $this->material->getName(),
                'linkedType' => 'material',
                'id' => $this->material->getId()
            ];
        }
        if ($this->tool) {
            return [
                'name' => $this->tool->getName(),
                'linkedType' => 'tool',
                'id' => $this->tool->getId()
            ];
        }
        if ($this->keyy) {
            return [
                'name' => $this->keyy->getName(),
                'linkedType' => 'keyy',
                'id' => $this->keyy->getId()
            ];
        }
        return null;
    }
    
    /**
     * @Groups({"list", "detail"})
     */
    public function getProfileImage(): ?string
    {
        /** @var File $file */
        foreach ($this->files as $index => $fileArray) {
            $file = File::fromArray($fileArray);
            if ($file->getDocType() === 'profileImage') {
                return $file->getRelativePath();
            }
        }
        return null;
    }
    
    public function getAllFiles(): array
    {
        return $this->files;
    }
    
    public function getLogData(): string
    {
        return $this->getId();
    }
}
