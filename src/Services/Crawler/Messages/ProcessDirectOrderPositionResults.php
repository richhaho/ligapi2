<?php

declare(strict_types=1);


namespace App\Services\Crawler\Messages;


class ProcessDirectOrderPositionResults
{
    /**
     * @var string[] $directOrderPositionResultIds
     */
    private array $directOrderPositionResultIds;
    
    public function __construct(array $directOrderPositionResultIds)
    {
        $this->directOrderPositionResultIds = $directOrderPositionResultIds;
    }
    
    public function getDirectOrderPositionResultIds(): array
    {
        return $this->directOrderPositionResultIds;
    }
}
