<?php

declare(strict_types=1);


namespace App\Services\BatchChanges;


use App\Api\Dto\ManyDto;
use App\Entity\Material;
use App\Entity\User;
use App\Services\BatchChanges\Messages\PatchMany;
use Symfony\Component\Messenger\MessageBusInterface;

class BatchChangesService
{
    private MessageBusInterface $messageBus;
    private int $batchSize;
    
    public function __construct(MessageBusInterface $messageBus, $batchSize = 50)
    {
        $this->messageBus = $messageBus;
        $this->batchSize = $batchSize;
    }
    
    private function dispatch(array $batch, array $query, User $user): void
    {
        $patchMany = new PatchMany($batch, $query, Material::class, $user->getId());
        $this->messageBus->dispatch($patchMany);
    }
    
    public function createBatchChangesMessengesForPatchMany(ManyDto $patchManyDto, User $user): void
    {
        $batch = [];
        $counter = 0;
        
        foreach ($patchManyDto->ids as $id) {
            if ($counter > $this->batchSize) {
                $counter = 0;
                $this->dispatch($batch, $patchManyDto->query, $user);
                $batch = [];
            } else {
                $batch[] = $id;
                $counter++;
            }
        }
        
        if (count($batch) > 0) {
            $this->dispatch($batch, $patchManyDto->query, $user);
        }
    }
}
