<?php

declare(strict_types=1);

namespace App\Entity\Data;

use App\Exceptions\Domain\InvalidArgumentException;
use Symfony\Component\Serializer\Annotation\Groups;

final class Permission
{
    public const NONE = 'NONE';
    
    public const READ = 'READ';

    public const BOOK = 'BOOK';

    public const EDIT = 'EDIT';

    public const CREATE = 'CREATE';

    public const DELETE = 'DELETE';
    
    public const ADMIN = 'ADMIN';

    private static array $permissionMap = [
        self::NONE => 0,
        self::READ => 2,
        self::BOOK => 4,
        self::EDIT => 8,
        self::CREATE => 16,
        self::DELETE => 32,
        self::ADMIN => 64
    ] ;
    
    /**
     * @Groups({"detail", "permission", "list"})
     */
    private string $category; // Class name or permission group id
    /**
     * @Groups({"detail", "permission", "list"})
     */
    private string $action;

    public function __construct(string $category, string $action)
    {
//        if (!class_exists($category)) {
//            // TODO: how to check for class name or entity object?
              // TODO: is_subclass_of()
//        }

        $this->category = $category;

        if (!in_array($action, array_keys(self::$permissionMap))) {
            throw InvalidArgumentException::forInvalidElement($action, implode('/', array_keys(self::$permissionMap)));
        }

        $this->action = $action;
    }

    public static function levels(): array
    {
        return array_keys(self::$permissionMap);
    }

    public static function fromArray(array $data): self
    {
        return new self($data['category'], $data['action']);
    }

    public function toArray(): array
    {
        return [
            'category' => $this->category,
            'action' => $this->action
        ];
    }

    public function includes(string $action): bool
    {
        return
            in_array($action, array_keys(self::$permissionMap)) &&
            self::$permissionMap[$action] <= self::$permissionMap[$this->action];
    }

    public function appliesToCategory(string $category): bool
    {
        return $this->category === $category;
    }
    
    public function getCategory(): string
    {
        return $this->category;
    }
    
    public function getAction(): string
    {
        return $this->action;
    }
    
    public function getAllTypes(): array
    {
        $permissionArray = [];
        switch ($this->getAction()) {
            case 'DELETE':
                $permissionArray[] = new self($this->getCategory(), 'DELETE');
            case 'CREATE':
                $permissionArray[] = new self($this->getCategory(), 'CREATE');
            case 'EDIT':
                $permissionArray[] = new self($this->getCategory(), 'EDIT');
            case 'BOOK':
                $permissionArray[] = new self($this->getCategory(), 'BOOK');
            case 'READ':
                $permissionArray[] = new self($this->getCategory(), 'READ');
        }
        
        return $permissionArray;
    }
}
