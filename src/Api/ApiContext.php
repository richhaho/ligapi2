<?php

declare(strict_types=1);

namespace App\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationAnnotation;

/**
 * @Annotation
 */
class ApiContext extends ConfigurationAnnotation
{
    public array $groups = [];
    public ?string $selfRoute = null;

    /**
     * @param array $groups
     */
    public function setGroups(array $groups): void
    {
        $this->groups = $groups;
    }

    /**
     * @param string $selfRoute
     */
    public function setSelfRoute(string $selfRoute): void
    {
        $this->selfRoute = $selfRoute;
    }

    public function getAliasName()
    {
        return 'api_context';
    }

    public function allowArray()
    {
        return false;
    }
}