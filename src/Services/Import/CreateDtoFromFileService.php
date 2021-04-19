<?php

declare(strict_types=1);


namespace App\Services\Import;

use App\Api\Dto\CreateKeyy;
use App\Api\Dto\CreateMaterialDto;
use App\Api\Dto\CreateTool;
use App\Api\Dto\CreateUser;
use App\Api\Dto\CustomerDto;
use App\Api\Dto\DtoInterface;
use App\Api\Dto\FileDto;
use App\Api\Dto\MaterialLocationDto;
use App\Api\Dto\OrderSourceDto;
use App\Api\Dto\ProjectDto;
use App\Api\Dto\StockChangeDto;
use App\Api\Dto\SupplierDto;
use App\Exceptions\Domain\ImportException;
use App\Exceptions\Domain\InvalidArgumentException;
use App\Services\Import\FileReaders\FileReaderInterface;
use Generator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CreateDtoFromFileService
{
    private string $dtoName;
    private DtoService $dtoService;
    private iterable $fileReaders;
    
    public function __construct(
        DtoService $dtoService,
        iterable $fileReaders
    )
    {
        $this->dtoService = $dtoService;
        $this->fileReaders = $fileReaders;
    }
    
    private function hydrate(array $header, array $lineArray): DtoInterface
    {
        $data = [];
        foreach ($header as $index => $item) {
            $data[$item] = $lineArray[$index];
        }
        
        return $this->dtoService->createDtoFromArray($data, $this->dtoName);
    }

    private function getDtos(Generator $lines): Generator
    {
        $header = $lines->current();
    
        $lines->next();
    
        while ($lines->current()) {
            $lineArray = $lines->current();
            $lines->next();
            yield $this->hydrate($header, $lineArray);
        }
    }
    
    public function arrayToGenerator(array $data): Generator
    {
        foreach ($data as $line) {
            yield $line;
        }
    }
    
    private function importCompleteXlsx(UploadedFile $file): Generator
    {
        $file = IOFactory::load($file->getRealPath());
        
        $sheets = $file->getAllSheets();
        
        foreach ($sheets as $sheet) {
            $sheetName = $sheet->getTitle();
            
            switch ($sheetName) {
                case 'users':
                    $dtoName = CreateUser::class;
                    break;
                case 'suppliers':
                    $dtoName = SupplierDto::class;
                    break;
                case 'materials':
                    $dtoName = CreateMaterialDto::class;
                    break;
                case 'locationLinks':
                    $dtoName = MaterialLocationDto::class;
                    break;
                case 'stockChanges':
                    $dtoName = StockChangeDto::class;
                    break;
                case 'orderSources':
                    $dtoName = OrderSourceDto::class;
                    break;
                case 'tools':
                    $dtoName = CreateTool::class;
                    break;
                case 'keyys':
                    $dtoName = CreateKeyy::class;
                    break;
                case 'files':
                    $dtoName = FileDto::class;
                    break;
                default:
                    throw InvalidArgumentException::forInvalidXlsxFile(sprintf('sheet name "%s" not supported', $sheetName));
            }
            
            $this->dtoName = $dtoName;
    
            $sheetLines = $sheet->toArray();
            
            $dtoGenerator = $this->getDtos($this->arrayToGenerator($sheetLines));
            
            while ($dtoGenerator->current()) {
                $dto = $dtoGenerator->current();
                $dtoGenerator->next();
                yield $dto;
            }
        }
    }
    
    private function setDtoName(UploadedFile $file): void
    {
        switch ($file->getClientOriginalName()) {
            case '1_Nutzer.xlsx':
                $this->dtoName = CreateUser::class;
                return;
            case '2_Lieferanten.xlsx':
                $this->dtoName = SupplierDto::class;
                return;
            case '3_Materialien.xlsx':
                $this->dtoName = CreateMaterialDto::class;
                return;
            case '4_Lagerorte.xlsx':
                $this->dtoName = MaterialLocationDto::class;
                return;
            case '5_Bestandsaenderungen.xlsx':
                $this->dtoName = StockChangeDto::class;
                return;
            case '6_Bezugsquellen.xlsx':
                $this->dtoName = OrderSourceDto::class;
                return;
            case '7_Werkzeuge.xlsx':
                $this->dtoName = CreateTool::class;
                return;
            case '8_Schluessel.xlsx':
                $this->dtoName = CreateKeyy::class;
                return;
            case '9_Dateien.xlsx':
                $this->dtoName = FileDto::class;
                return;
            case '10_Kunden.xlsx':
                $this->dtoName = CustomerDto::class;
                return;
            case '11_Projekte.xlsx':
                $this->dtoName = ProjectDto::class;
                return;
        }
        throw InvalidArgumentException::forUnsupportedFile($file->getClientOriginalName());
    }
    
    public function getDtoGeneratorFromFile(UploadedFile $file): Generator
    {
        $this->setDtoName($file);
        
        $supported = false;
        
        /** @var FileReaderInterface $fileReader */
        foreach ($this->fileReaders as $fileReader) {
            if ($fileReader->supports($file)) {
                $supported = true;
                $fileReader->construct($file);
                if ($fileReader->isComplex($file)) {
                    $importComplexXlsxGenerater = $this->importCompleteXlsx($file);
                    while ($importComplexXlsxGenerater->current()) {
                        $res = $importComplexXlsxGenerater->current();
                        $importComplexXlsxGenerater->next();
                        yield $res;
                    }
                } else {
                    $lines = $fileReader->getDataLines();
                    $dtoGenerator = $this->getDtos($lines);
                    while ($dtoGenerator->current()) {
                        $dto = $dtoGenerator->current();
                        $dtoGenerator->next();
                        yield $dto;
                    }
                }
            }
        }
        
        if (!$supported) {
            throw ImportException::forInvalidFileType($file->getClientOriginalName());
        }
    }
}
