<?php


namespace App\Api\Mapper;


use App\Api\Dto\BatchUpdateDtoInterface;

interface MapperWithBatchUpdateInterface extends MapperInterface
{
    public function batchUpdateFromDto(BatchUpdateDtoInterface $batchUpdateBatchUpdateDto): iterable;
}
