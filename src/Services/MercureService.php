<?php

declare(strict_types=1);


namespace App\Services;


use Symfony\Component\Mercure\PublisherInterface;
use Symfony\Component\Mercure\Update;

class MercureService
{
    
    private PublisherInterface $publisher;
    
    public function __construct(PublisherInterface $publisher)
    {
        $this->publisher = $publisher;
    }
    
    public function sendMessage(string $topic, array $message): string
    {
        $update = new Update(
            $topic,
            json_encode($message)
        );
    
        $publisher = $this->publisher;
    
        // The Publisher service is an invokable object
        $publisher($update);
    
        return 'published!';
    }
}
