<?php


namespace App\Api\Dto;


use App\Entity\Data\EntityType;

interface BaseEntityDtoInterface
{
    public function getEntityType(): EntityType;
}
