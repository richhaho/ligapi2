<?php

declare(strict_types=1);


namespace App\Services\Crawler;


use App\Services\FileService;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Downloader
{
    
    private HttpClientInterface $httpClient;
    private FileService $fileService;
    
    public function __construct(
        HttpClientInterface $httpClient,
        FileService $fileService
    )
    {
        $this->httpClient = $httpClient;
        $this->fileService = $fileService;
    }
    
    public function downloadCompanyUrl(string $url): File
    {
        $response = $this->httpClient->request('GET', $url, ["verify_peer"=>false,"verify_host"=>false]);
        
        $tempUrl = tempnam(sys_get_temp_dir(), 'ligapiimage');
        
        file_put_contents($tempUrl, $response->getContent());
        
        return new File($tempUrl);
    }
}
