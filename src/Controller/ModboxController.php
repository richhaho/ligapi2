<?php

declare(strict_types=1);


namespace App\Controller;


use App\Api\Dto\ModboxDto;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/modbox", name="api_modbox_")
 */
class ModboxController
{
    /**
     * @Route(name="index", methods={"POST"})
     */
    public function index(ModboxDto $modboxDto): array
    {
        return [
            'status' => 'Alrighty then!',
            'id' => $modboxDto->id,
            'data' => $modboxDto->data
        ];
    }
}
