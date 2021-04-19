<?php

declare(strict_types=1);


namespace App\Entity;


use DateTimeImmutable;

interface DeleteUpdateAwareInterface
{
    public function setDeleted(bool $deleted): void;
    public function setUpdatedAt(DateTimeImmutable $updatedAt): void;
}
