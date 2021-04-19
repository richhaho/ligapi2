<?php


namespace App\Entity;


interface SearchableInterface
{
    public function getSearchableText(): string;
    public function getName(): string;
    public function getId(): string;
    public function getCompany(): Company;
}
