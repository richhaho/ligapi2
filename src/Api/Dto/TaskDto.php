<?php

declare(strict_types=1);


namespace App\Api\Dto;


use Symfony\Component\Validator\Constraints as Assert;

class TaskDto
{
    public ?string $id = null;
    
    /**
     * @Assert\Length(max=255)
     * @Assert\Length(min=1)
     * @Assert\NotBlank()
     */
    public string $topic;
    
    /**
     * @Assert\Length(max=255)
     * @Assert\Length(min=1)
     * @Assert\NotBlank()
     */
    public string $responsible;
    public ?string $details = null;
    public ?string $startDate = null;
    public ?string $dueDate = null;
    public ?string $materialId = null;
    public ?string $toolId = null;
    public ?string $keyyId = null;
    public ?int $priority = null;
    public ?string $taskStatus = null;
}
