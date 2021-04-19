<?php

declare(strict_types=1);


namespace App\Api\Mapper;


use App\Api\Dto\LocationCollectionDto;
use App\Api\Dto\PutCompany;
use App\Api\Dto\PutCompanySettings;
use App\Entity\Company;
use App\Entity\Data\AppSettings;
use App\Entity\Data\PaymentCycleType;
use App\Entity\Data\PaymentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CompanyMapper
{
    use ValidationTrait;
    
    private ValidatorInterface $validator;
    private EntityManagerInterface $entityManager;
    
    public function __construct(ValidatorInterface $validator, EntityManagerInterface $entityManager)
    {
        $this->validator = $validator;
        $this->entityManager = $entityManager;
    }
    
    public function putCompanyFromDto(PutCompany $putCompany, Company $company): Company
    {
        $putCompany->id = $company->getId();
    
        $this->validate($putCompany);
        
        $company->setName($putCompany->name);
        $company->setCity($putCompany->city);
        $company->setCountry($putCompany->country);
        $company->setInvoiceEmail($putCompany->invoiceEmail);
        $company->setOrderEmail($putCompany->orderEmail);
        $company->setFax($putCompany->fax);
        $company->setPhone($putCompany->phone);
        $company->setStreet($putCompany->street);
        $company->setWebsite($putCompany->website);
        $company->setZip($putCompany->zip);
        $company->setAddressLine1($putCompany->addressLine1);
        $company->setAddressLine2($putCompany->addressLine2);
        $company->setCurrentMaterialLabel($putCompany->currentMaterialLabel);
        $company->setUserAmount($putCompany->userAmount);
        
        $paymentCycle = null;
        if ($putCompany->paymentCycle) {
            $paymentCycle = PaymentCycleType::fromString($putCompany->paymentCycle);
        }
        $company->setPaymentCycle($paymentCycle);
        
        $paymentType = null;
        if ($putCompany->paymentType) {
            $paymentType = PaymentType::fromString($putCompany->paymentType);
        }
        $company->setPaymentType($paymentType);
        
        return $company;
    }
    
    public function putCompanySettingsFromDto(PutCompanySettings $putCompanySettings, Company $company): Company
    {
        $company->setAppSettings(AppSettings::fromArray($putCompanySettings->appSettings));
        $company->setCustomMaterialName($putCompanySettings->customMaterialName);
        $company->setCustomToolName($putCompanySettings->customToolName);
        $company->setCustomKeyyName($putCompanySettings->customKeyyName);
        $company->setCustomMaterialsName($putCompanySettings->customMaterialsName);
        $company->setCustomToolsName($putCompanySettings->customToolsName);
        $company->setCustomKeyysName($putCompanySettings->customKeyysName);
        
        return $company;
    }
    
    public function putLocationCollectionFromDto(LocationCollectionDto $locationCollectionDto, Company $company): Company
    {
        $this->validate($locationCollectionDto);
        
        $company->setCollectionLocations($locationCollectionDto->locationCollection);
        
        return $company;
    }
}
