<?php

declare(strict_types=1);


namespace App\Services\Import\Transformers;


use App\Api\Dto\DtoInterface;
use DateTime;

class DateTransformer implements TransformerInterface
{
    
    public function supports(string $transformer): bool
    {
        return $transformer === DateTransformer::class;
    }
    
    public function transform(array $data, string $property, DtoInterface $dto, string $title): string
    {
        $date = $data[$title];
        if (!$date) {
            return '';
        }
        if (strpos($date, '-')) {
            if (strlen($date) === 8) {
                return DateTime::createFromFormat('y-m-d', $date)->format('Y-m-d');
            }
            if (strlen($date) === 10) {
                return DateTime::createFromFormat('Y-m-d', $date)->format('Y-m-d');
            }
        }
        if (strpos($date, '/')) {
            $date = explode(' ', $date)[0];
            $dateParts = explode('/', $date);
            $month = $dateParts[0];
            $day = $dateParts[1];
            $year = $dateParts[2];
            return $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
        }
        if (strlen($date) === 8) {
            return DateTime::createFromFormat('d.m.y', $date)->format('Y-m-d');
        }
        if (strlen($date) === 10) {
            return DateTime::createFromFormat('d.m.Y', $date)->format('Y-m-d');
        }
        return '';
    }
}
