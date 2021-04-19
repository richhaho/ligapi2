<?php

declare(strict_types=1);


namespace App\Entity;


interface CompanyAwareInterface
{
    public function getCompany(): Company;
}
