<?php

declare(strict_types=1);


namespace App\Services\Import\FileReaders;


use Generator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class XlsxFileReader implements FileReaderInterface
{
    private Spreadsheet $file;
    
    public function construct(UploadedFile $file)
    {
        $this->file = IOFactory::load($file->getRealPath());
    }
    
    public function supports(UploadedFile $file): bool
    {
        return str_contains($file->getClientOriginalName(), '.xlsx');
    }
    
    public function isComplex(UploadedFile $file): bool
    {
        return count($this->file->getAllSheets()) > 1;
    }
    
    public function getDataLines(): Generator
    {
        $firstSheet = $this->file->getSheet(0);
        
        $data = $firstSheet->toArray();
        
        foreach ($data as $line) {
            yield $line;
        }
    }
}
