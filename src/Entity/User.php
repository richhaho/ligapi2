<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Data\Permission;
use App\Event\Log;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity()
 */
class User implements UserInterface, CompanyAwareInterface, LoggableInterface, DeleteUpdateAwareInterface
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string")
     * @Groups({"list", "detail"})
     */
    private string $id;
    
    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"detail", "list"})
     */
    private DateTimeImmutable $createdAt;
    
    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"detail", "list"})
     */
    private ?DateTimeImmutable $updatedAt = null;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private bool $deleted;
    
    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private ?DateTimeImmutable $lastLogin = null;

    /**
     * @ORM\Column(type="string", unique=true)
     * @Log()
     * @Groups({"list", "detail"})
     */
    private string $email;

    /**
     * @ORM\Column(type="string")
     */
    private ?string $password = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="users", fetch="EAGER")
     */
    private Company $company;
    
    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Location", mappedBy="user")
     */
    private ?Location $location = null;

    /**
     * @ORM\Column(type="json")
     * @Log()
     * @Groups({"detail", "list"})
     */
    private iterable $permissions;
    
    /**
     * @ORM\Column(type="boolean")
     * @Log()
     * @Groups({"detail", "permission", "list"})
     */
    private bool $isAdmin;
    
    /**
     * @ORM\Column(type="string", length=255)
     * @Log()
     * @Groups({"list", "detail"})
     */
    private string $firstName;
    
    /**
     * @ORM\Column(type="string", length=255)
     * @Log()
     * @Groups({"list", "detail"})
     */
    private string $lastName;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $passwordResetToken = null;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $passwordResetExpires = null;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $webRefreshToken = null;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $mobileRefreshToken = null;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $deviceUuid = null;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Task", mappedBy="responsible")
     */
    private Collection $tasks;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Consignment", mappedBy="user")
     */
    private Collection $consignments;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\StockChange", mappedBy="user")
     */
    private Collection $stockChanges;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\DirectOrder", mappedBy="user")
     */
    private Collection $directOrders;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Material", mappedBy="orderStatusChangeUserToOrder")
     */
    private Collection $orderStatusChangeMaterialsToOrder;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Material", mappedBy="orderStatusChangeUserOnItsWay")
     */
    private Collection $orderStatusChangeMaterialsOnItsWay;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Material", mappedBy="orderStatusChangeUserAvailable")
     */
    private Collection $orderStatusChangeMaterialsAvailable;
    
    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private bool $doubleOptIn = false;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $mobilePushId = null;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $webPushId = null;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\GridState", mappedBy="user")
     */
    private Collection $gridStates;

    public function __construct(string $firstName, string $lastName, string $email, Company $company)
    {
        $this->id = Uuid::uuid4()->toString();
        $this->createdAt = new DateTimeImmutable();
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->company = $company;
        $this->stockChanges = new ArrayCollection();
        $this->orderStatusChangeMaterialsToOrder = new ArrayCollection();
        $this->orderStatusChangeMaterialsOnItsWay = new ArrayCollection();
        $this->orderStatusChangeMaterialsAvailable = new ArrayCollection();
        $this->directOrders = new ArrayCollection();
        $this->gridStates = new ArrayCollection();
        $this->isAdmin = false;
        $this->deleted = false;
        $this->permissions = [
            [
                "category" => "material",
                "action" => "NONE"
            ],
            [
                "category" => "tool",
                "action" => "NONE"
            ],
            [
                "category" => "keyy",
                "action" => "NONE"
            ],
            [
                "category" => "project",
                "action" => "NONE"
            ],
        ];
        $this->tasks = new ArrayCollection();
        $this->consignments = new ArrayCollection();
    }

    public function updatePassword(string $newPassword, UserPasswordEncoderInterface $encoder)
    {
        $this->password = $encoder->encodePassword($this, $newPassword);
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getSalt()
    {
        return null;
    }

    public function getUsername(): string
    {
        return $this->email;
    }

    public function eraseCredentials()
    {
        // noop
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getCompany(): Company
    {
        return $this->company;
    }

    public function getPermissions(): iterable
    {
        $permissions = [];

        foreach ($this->permissions as $entry) {
            $permissions[] = Permission::fromArray($entry);
        }

        return $permissions;
    }

    public function setPermissions(array $permissions): void
    {
        $this->permissions = [];

        /** @var Permission $permission */
        foreach ($permissions as $permission) {
            $this->permissions[] = $permission->toArray();
        }
    }
    
    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }
    
    public function setIsAdmin(bool $isAdmin): void
    {
        $this->isAdmin = $isAdmin;
    }
    
    public function getFirstName(): string
    {
        return $this->firstName;
    }
    
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }
    
    public function getLastName(): string
    {
        return $this->lastName;
    }
    
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }
    
    public function getPasswordResetToken(): ?string
    {
        return $this->passwordResetToken;
    }
    
    public function setPasswordResetToken(?string $passwordResetToken): void
    {
        $this->passwordResetToken = $passwordResetToken;
    }
    
    public function getPasswordResetExpires(): ?string
    {
        return $this->passwordResetExpires;
    }
    
    public function setPasswordResetExpires(?int $passwordResetExpires): void
    {
        $this->passwordResetExpires = $passwordResetExpires;
    }
    
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }
    
    /**
     * @Groups({"shoppingCart", "orderedMaterials"})
     */
    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->getLastName();
    }
    
    public function getWebRefreshToken(): ?string
    {
        return $this->webRefreshToken;
    }
    
    public function setWebRefreshToken(?string $webRefreshToken): void
    {
        $this->webRefreshToken = $webRefreshToken;
    }
    
    public function getLogData(): string
    {
        return $this->getFullName();
    }
    
    public function getConsignments()
    {
        return $this->consignments;
    }
    
    public function getLocation(): ?Location
    {
        return $this->location;
    }
    
    public function getTasks()
    {
        return $this->tasks;
    }
    
    public function getStockChanges()
    {
        return $this->stockChanges;
    }
    
    public function getDeviceUuid(): ?string
    {
        return $this->deviceUuid;
    }
    
    public function setDeviceUuid(?string $deviceUuid): void
    {
        $this->deviceUuid = $deviceUuid;
    }
    
    public function isDoubleOptIn(): bool
    {
        return $this->doubleOptIn;
    }
    
    public function setDoubleOptIn(bool $doubleOptIn): void
    {
        $this->doubleOptIn = $doubleOptIn;
    }
    
    public function getMobilePushId(): ?string
    {
        return $this->mobilePushId;
    }
    
    public function setMobilePushId(?string $mobilePushId): void
    {
        $this->mobilePushId = $mobilePushId;
    }
    
    public function getWebPushId(): ?string
    {
        return $this->webPushId;
    }
    
    public function setWebPushId(?string $webPushId): void
    {
        $this->webPushId = $webPushId;
    }
    
    public function getMobileRefreshToken(): ?string
    {
        return $this->mobileRefreshToken;
    }
    
    public function setMobileRefreshToken(?string $mobileRefreshToken): void
    {
        $this->mobileRefreshToken = $mobileRefreshToken;
    }
    
    /**
     * @Groups({"permission"})
     */
    public function getPermissionFragments(): array
    {
        $permissions = [];
        
        foreach ($this->permissions as $entry) {
            $permission = Permission::fromArray($entry);
            foreach ($permission->getAllTypes() as $permissionFragment) {
                $permissions[] = $permissionFragment;
            }
        }
        
        return $permissions;
    }
    
    public function getLastLogin(): ?DateTimeImmutable
    {
        return $this->lastLogin;
    }
    
    public function setLastLogin(?DateTimeImmutable $lastLogin): void
    {
        $this->lastLogin = $lastLogin;
    }
    
    public function isDeleted(): bool
    {
        return $this->deleted;
    }
    
    public function setDeleted(bool $deleted): void
    {
        $this->deleted = $deleted;
    }
    
    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }
    
    public function setUpdatedAt(DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
    
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
