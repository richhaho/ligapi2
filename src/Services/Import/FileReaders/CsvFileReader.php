<?php

declare(strict_types=1);


namespace App\Services\Import\FileReaders;


use Generator;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CsvFileReader implements FileReaderInterface
{
    private $file;
    
    public function construct(UploadedFile $file)
    {
        $this->file = fopen($file->getPath(), 'r');
    }
    
    public function __destruct()
    {
        if ($this->file) {
            fclose($this->file);
        }
    }
    
    public function supports(UploadedFile $file): bool
    {
        return str_contains($file->getClientOriginalName(), '.csv');
    }
    
    public function getDataLines(): Generator
    {
        rewind($this->file);
        
        while ($line = fgetcsv($this->file)) {
            yield $line;
        }
    }
    
    public function isComplex(UploadedFile $file): bool
    {
        return false;
    }
}
