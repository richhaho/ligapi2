<?php

declare(strict_types=1);


namespace App\Controller;


use App\Api\ApiContext;
use App\Api\Dto\ScannerDto;
use App\Api\Dto\SearchDto;
use App\Exceptions\Domain\MissingDataException;
use App\Repository\KeyyRepository;
use App\Repository\MaterialRepository;
use App\Repository\SearchIndexRepository;
use App\Repository\ToolRepository;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/api/general/", name="api_general_")
 */
class GeneralItemController
{
    /**
     * @Route(path="getentities", name="get_by_get_by_code", methods={"POST"})
     * @ApiContext(groups={"list", "scannerResult"})
     */
    public function get_by_code(
        ScannerDto $scannerDto,
        MaterialRepository $materialRepository,
        ToolRepository $toolRepository,
        KeyyRepository $keyyRepository
    ): array
    {
        if (!$scannerDto->code || $scannerDto->code === '') {
            throw MissingDataException::forMissingData('code');
        }
        
        $code = $scannerDto->code;
    
        $result = [];
        
        if ($scannerDto->itemType) {
            switch ($scannerDto->itemType) {
                case 'material':
                    $result = $materialRepository->findByCode($code);
                    break;
                case 'tool':
                    $result = $toolRepository->findByCode($code);
                    break;
                case 'keyy':
                    $result = $keyyRepository->findByCode($code);
                    break;
            }
        } else {
            $result = [
                ... $materialRepository->findByCode($code),
                ... $toolRepository->findByCode($code),
                ... $keyyRepository->findByCode($code)
            ];
        }
        
        return $result;
    }
    
    /**
     * @Route(path="getsearchresult", name="get_by_searchterm", methods={"POST"})
     * @ApiContext(groups={"list"})
     */
    public function get_by_searchterm(
        SearchDto $searchDto,
        SearchIndexRepository $searchIndexRepository
    ): array
    {
        if (!$searchDto->searchterm || $searchDto->searchterm === '') {
            throw MissingDataException::forMissingData('searchterm');
        }
        
        return $searchIndexRepository->getCategorizedSearchResults($searchDto);
    }
}
