<?php


namespace App\Exceptions\Domain;


use App\Api\Dto\BatchUpdateDtoInterface;
use App\Entity\Material;
use Money\Money;

class InvalidArgumentException extends \InvalidArgumentException implements DomainException, UserReadableException, HttpException
{
    private string $userMessage;
    
    public function __construct(string $apiMessage, ?string $userMessage = null)
    {
        parent::__construct($apiMessage);
        if ($userMessage) {
            $this->userMessage = $userMessage;
        } else {
            $this->userMessage = $apiMessage;
        }
    }
    
    public static function forInvalidRange($value, string $rangeDescription): self
    {
        return new self(sprintf('Value %s is out of range. It should be %s', $value, $rangeDescription));
    }
    
    public static function forInvalidElement($value, string $allowedDescription): self
    {
        $value = (string)$value;
    
        return new self(sprintf('Value %s is not allowed. It should be %s', $value, $allowedDescription));
    }
    
    public static function forUnsupportedMoney(Money $money): self
    {
        return new self(sprintf('Class %s is not supported. It should be Money\Money.', get_class($money)));
    }
    
    public static function forUnsupportedLocationType(string $id, string $name): self
    {
        return new self(sprintf('Name of user location with id %s can not be set to %s.', $id, $name));
    }
    
    public static function forUnsupportedMimeType(string $relativePath, string $mimeType, string $allowed): self
    {
        return new self(sprintf('MimeType %s of file %s is not supported. Supported is %s', $mimeType, $relativePath, $allowed));
    }
    
    public static function forEntityDoesNotSupportFiles(string $entityId, string $class): self
    {
        return new self(sprintf('Entity with id %s of class %s does not support files.', $entityId, $class));
    }
    
    public static function forInvalidFile(string $relativePath): self
    {
        return new self(sprintf('File %s is not valid.', $relativePath));
    }
    
    public static function forInvalidTaskDates(string $startDate, string $dueDate, string $id): self
    {
        return new self(sprintf('Task dates for %id are invalid. Start date has to be after due date. Start is %s and due is %s.', $id, $startDate, $dueDate));
    }
    
    public static function forInvalidEntityType(string $providedType, string $requiredType): self
    {
        return new self(sprintf('Entity type %s is not supported. Only %s is supported.', $providedType, $requiredType));
    }
    
    public static function forBatchUpdateNotAllowed(string $string): self
    {
        return new self(sprintf('Batch update of field  %s is not supported.', $string));
    }
    
    public static function forInvalidXlsxFile(string $reason): self
    {
        return new self(sprintf('Xlsx file is invalid. %s', $reason));
    }
    
    public static function forMainLocationLinkMissing(Material $material): self
    {
        return new self(
            sprintf('Material with number %s does not have a main location.', $material->getitemNumber()),
            sprintf('Material mit Nummer %s hat kein Hauptlager.', $material->getitemNumber())
        );
    }
    
    public static function forUnsupportedDto(BatchUpdateDtoInterface $batchUpdateDto): self
    {
        return new self(
            sprintf('%s is not supported.', get_class($batchUpdateDto)),
            sprintf('%s wird nicht unterstützt.', get_class($batchUpdateDto))
        );
    }
    
    public static function forUnsupportedFile(string $fileName): self
    {
        return new self(
            sprintf('%s is not supported.', $fileName),
            sprintf('%s wird nicht unterstützt.', $fileName)
        );
    }
    
    public function getUserMessage(): string
    {
        return 'Fehler! ' . $this->userMessage;
    }
    
    public function getStatusCode(): int
    {
        return 400;
    }

}
