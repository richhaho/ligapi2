<?php

declare(strict_types=1);

namespace App\Api;

use App\Entity\Keyy;

class RouteMap
{
    public const COLLECTION = 'collection';
    public const SELF = 'self';
    
    //TODO: Wofür nochmals da?
    private array $map = [
        self::COLLECTION => [
            Keyy::class => 'api_keyy_index',
        ],
        self::SELF => [
            Keyy::class => 'api_keyy_get',
        ]
    ];

    public function getRoute(string $type, string $class): string
    {
        if (!isset($this->map[$type][$class])) {
            // TODO: Exception
        }

        return $this->map[$type][$class];
    }
}
