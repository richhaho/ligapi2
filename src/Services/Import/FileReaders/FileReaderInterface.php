<?php


namespace App\Services\Import\FileReaders;

use Generator;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface FileReaderInterface
{
    public function supports(UploadedFile $file): bool;
    public function isComplex(UploadedFile $file): bool;
    public function getDataLines(): Generator;
    public function construct(UploadedFile $file);
}
