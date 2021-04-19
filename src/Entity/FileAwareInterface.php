<?php

declare(strict_types=1);


namespace App\Entity;


use App\Entity\Data\File;

interface FileAwareInterface
{
    public function getCompany(): Company;
    public function getFiles(): array;
    
    /**
     * @return array
     */
    public function getAllFiles(): array;
    public function addFile(File $fileToAdd): void;
    public function updateFile(File $fileToUpdate): void;
    public function removeFile(File $fileToRemove): void;
    public function getThumb(): ?string;
    public function getThumbFile(): ?File;
}
