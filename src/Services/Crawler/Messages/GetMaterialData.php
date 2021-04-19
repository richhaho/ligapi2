<?php

declare(strict_types=1);


namespace App\Services\Crawler\Messages;


class GetMaterialData
{
    /**
     * @var string[] $materialIds
     */
    private array $materialIds;
    
    public function __construct(array $materialIds)
    {
        $this->materialIds = $materialIds;
    }
    
    public function getMaterialIds(): array
    {
        return $this->materialIds;
    }
    
}
