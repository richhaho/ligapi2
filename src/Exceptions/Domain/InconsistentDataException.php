<?php

declare(strict_types=1);


namespace App\Exceptions\Domain;


use App\Entity\Project;
use RuntimeException;

class InconsistentDataException extends RuntimeException implements DomainException, UserReadableException, HttpException
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
    
    public static function forDublicateMainLocation(string $materialName, string $mateiralId, string $newLocationName): self
    {
        return new self(sprintf('Main material location already exists. Tried to add location %s to %s: %s', $newLocationName, $mateiralId, $materialName));
    }
    
    public static function forDublicateLocationName(string $newLocationName): self
    {
        return new self(sprintf('Location with same name already exists for material. Tried to add location %s', $newLocationName));
    }
    
    public static function forDublicateProjectName(string $projectName): self
    {
        return new self(sprintf('Material already has materialLocation with same project. Tried to add project %s', $projectName));
    }
    
    public static function forDublicateSupplier(string $supplierName): self
    {
        return new self(sprintf('Ordersource with same supplier already exists for material. Tried to add supplier %s', $supplierName));
    }
    
    public static function forDublicateUserName(string $firstName, string $lastName): self
    {
        return new self(sprintf('User with same first and last name already exists. Tried to add %s %s', $firstName, $lastName));
    }
    
    public static function forSupplierOfMaterialDoesNotExist(string $supplierId, string $materialId): self
    {
        return new self(sprintf('For material with id %s does not exist an order source with id %s', $materialId, $supplierId));
    }
    
    public static function forAutoSupplierMissing(string $materialId): self
    {
        return new self(sprintf('Auto Supplier is missing for material id %s', $materialId));
    }
    
    public static function forDataIsMissing(string $data): self
    {
        return new self(sprintf('%s is missing', $data));
    }
    
    public static function forProjectNameCantBeChanged(string $projectId): self
    {
        return new self(sprintf('Consignment name cannot be changed. User or Project connected. ID: %s', $projectId));
    }
    
    public static function forMultipleConsignmentIdentifiersSet(string $name): self
    {
        return new self(sprintf('Consignment could not be created. Multiple identifiers set. One of them: %s', $name));
    }
    
    public static function forMissingMaterialOrderPositionData(string $name): self
    {
        return new self(sprintf('Order could not be created. Position data is incomplete: %s is missing.', $name));
    }
    
    public static function forUserMissesPermissionGroup(string $permissionGroupId): self
    {
        return new self(sprintf('User could not be created. Missing PermissionGroup: %s.', $permissionGroupId));
    }
    
    public static function forProjectHasStockChanges(Project $project): self
    {
        return new self(sprintf('Project "%s" could not be deleted. There are related stock changes.', $project->getName()));
    }
    
    public static function forDublicateOrderNumber(string $orderNumber): self
    {
        return new self(sprintf('There already exists an order source with the same orderNumber "%s" ', $orderNumber));
    }
    
    public static function forNegativeStock($locationName, $newStock): self
    {
        return new self(
            sprintf('Stock for %s could not be updated. It has negative stock %s ', $locationName, $newStock),
            sprintf('Bestand von %s konnte nicht aktualisiert werden. Negativer Bestand %s ', $locationName, $newStock)
        );
    }
    
    public static function forNegativePrice($orderNumber, $newPrice): self
    {
        return new self(sprintf('Price for %s could not be updated. It is negative %s ', $orderNumber, $newPrice));
    }
    
    public static function forTermsNotAccepted(): self
    {
        return new self("Company could not be created. Terms need to be accepted.");
    }
    
    public static function forPdfFieldCountDoesNotMatchSpecification(int $fieldsCount, int $specificationCount, string $type, string $name): self
    {
        return new self(sprintf("Label field could not be created for %s. Fieldcount %s must match %s specification field count %s.", $name, $fieldsCount, $type, $specificationCount));
    }
    
    public static function forInvalidDatanormFile(string $description): self
    {
        return new self(sprintf("Wrong DataNorm format. %s.", $description));
    }
    
    public static function forDtoMustImplementBaseEntityDto(): self
    {
        return new self('Dto must implement base entity dto');
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
