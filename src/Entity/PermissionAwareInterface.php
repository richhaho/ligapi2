<?php


namespace App\Entity;


interface PermissionAwareInterface
{
    public function getPermissionType(): string;
}
