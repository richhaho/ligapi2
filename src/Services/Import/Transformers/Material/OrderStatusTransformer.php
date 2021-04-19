<?php

declare(strict_types=1);


namespace App\Services\Import\Transformers\Material;


use App\Api\Dto\DtoInterface;
use App\Services\Import\Transformers\TransformerInterface;

class OrderStatusTransformer implements TransformerInterface
{
    
    public function supports(string $transformer): bool
    {
        return $transformer === OrderStatusTransformer::class;
    }
    
    public function transform(array $data, string $property, DtoInterface $dto, string $title): string
    {
        switch ($data['Bestellstatus']) {
            case 'unterwegs':
                return 'onItsWay';
            case 'bestellen':
                return 'toOrder';
        }
        return 'available';
    }
}
