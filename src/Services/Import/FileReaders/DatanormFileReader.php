<?php

declare(strict_types=1);


namespace App\Services\Import\FileReaders;


use App\Exceptions\Domain\InconsistentDataException;
use Generator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\RequestContext;

class DatanormFileReader implements FileReaderInterface
{
    private $file;
    private string $fileName;
    private RequestContext $requestContext;
    
    public function __construct(RequestContext $requestContext)
    {
        $this->requestContext = $requestContext;
    }
    
    public function construct(UploadedFile $file)
    {
        $this->requestContext->setParameter('changeSource', 'datanorm');
        $this->file = fopen($file->getPath(), 'r');
        $this->fileName = $file->getClientOriginalName();
    }
    
    public function __destruct()
    {
        if ($this->file) {
            fclose($this->file);
        }
    }
    
    private function getDataLine(string $line, string $supplierName): array
    {
        $line = preg_replace( "/\r|\n/", "", $line );
        $lineArray = explode(';', $line);
        
        if ($lineArray[2] !== $lineArray[15]) {
            throw InconsistentDataException::forInvalidDatanormFile('Order numbers from A and B line do not match:' . $lineArray[2] . '/' . $lineArray[15]);
        }
        
        return [
            'orderNumber'  => $lineArray[2],
            'name' => $lineArray[4] . ' ' . $lineArray[5],
            'sellingPrice' => $lineArray[9] ? (int) $lineArray[9] / 100 : '',
            'orderAmount' => pow(10,(int) $lineArray[7]),
            'unit' => $lineArray[8],
            'barcode' => $lineArray[22],
            'supplierName' => $supplierName
        ];
    }
    
    public function supports(UploadedFile $file): bool
    {
        return str_contains($file->getClientOriginalName(), '.0');
    }
    
    public function getDataLines(): Generator
    {
        rewind($this->file);
        
        $firstLine = '';
        $header = '';
    
        $supplierName = basename($this->fileName, '.001');
        
        while ($line = fgets($this->file)) {
            if ($header === '') {
                $header = $line;
            }
            if (substr($line, 0, 1) === 'A') {
                $firstLine = $line;
                continue;
            }
            if (substr($line, 0, 1) === 'B') {
                $secondLine = $line;
                yield $this->getDataLine($firstLine . $secondLine, $supplierName);
            }
        }
    }
    
    public function isComplex(UploadedFile $file): bool
    {
        return false;
    }
}
