<?php

declare(strict_types=1);


namespace App\Services\Import\Transformers;


use App\Api\Dto\DtoInterface;
use App\Services\Crawler\Downloader;
use Symfony\Component\HttpFoundation\File\File;

class ProfileImageTransformer implements TransformerInterface
{
    
    private Downloader $downloader;
    
    public function __construct(Downloader $downloader)
    {
        $this->downloader = $downloader;
    }
    
    public function supports(string $transformer): bool
    {
        return $transformer === ProfileImageTransformer::class;
    }
    
    public function transform(array $data, string $property, DtoInterface $dto, string $title): ?File
    {
        if (!$data[$title]) {
            return null;
        }
        return $this->downloader->downloadCompanyUrl($data[$title]);
    }
}
