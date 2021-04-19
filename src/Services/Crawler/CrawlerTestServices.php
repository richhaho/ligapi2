<?php

declare(strict_types=1);


namespace App\Services\Crawler;


use App\Api\Dto\AutoMaterialDto;
use App\Entity\Company;
use App\Entity\ConnectedSupplier;
use App\Entity\Data\AutoStatus;
use App\Entity\Material;
use App\Entity\OrderSource;
use App\Entity\Supplier;
use App\Exceptions\Domain\InconsistentDataException;
use App\Services\Crawler\Dto\AvailabilityInfosDto;

class CrawlerTestServices
{
    
    
    private Crawler $crawler;
    
    public function __construct(Crawler $crawler)
    {
        $this->crawler = $crawler;
    }
    
    private function testGetMaterialGC(): AutoMaterialDto
    {
        $company = new Company('command', true);
        
        $connectedSupplier = new ConnectedSupplier('GC', 'https://gconlineplus.de/');
        
        $supplier = new Supplier('GC', $company);
        $supplier->setWebShopLogin('LIGLagerbestellung');
        $supplier->setwebShopPassword('JoNp9ZOzIep6dmNB8NfbGacXdQ5RfNTRxQo+urqXJELoXg9sbd3To7F4IjIArPKaveU9MvYpQxhbnuOXvohWS/JR');
        $supplier->setConnectedSupplier($connectedSupplier);
    
        $material = new Material('202', 'new GC', $company);
        $material->setAutoSearchTerm('MEW20K');
        $material->setAutoSupplier($supplier);
        $material->setAutoStatus(AutoStatus::new());
    
        return $this->crawler->getMaterialDataForOneMaterial($material, $supplier, true);
    }
    
    private function testGetMaterialPM(): AutoMaterialDto
    {
        $company = new Company('command', true);
    
        $connectedSupplier = new ConnectedSupplier('PFEIFFER & MAY', 'https://online.pfeiffer-may.de:8443');
    
        $supplier = new Supplier('PFEIFFER & MAY', $company);
        $supplier->setWebShopLogin('kontakt@steffengrell.de');
        $supplier->setwebShopPassword('cEsuHodPHfEbtiTdr7sh+KWE/yzf+gTB6qfNTZGWIRxaFBWYyLUTVSDSXHNKAFiy7MIbZmLZ0Q==');
        $supplier->setConnectedSupplier($connectedSupplier);
    
        $material = new Material("pm1", 'new pm', $company);
        $material->setAutoSearchTerm('MEW20K');
        $material->setAutoSupplier($supplier);
        $material->setAutoStatus(AutoStatus::new());
    
        return $this->crawler->getMaterialDataForOneMaterial($material, $supplier, true);
    }
    
    private function testGetMaterialReisser(): AutoMaterialDto
    {
        $company = new Company('command', true);
    
        $connectedSupplier = new ConnectedSupplier('Reisser', 'https://reisser.sct.de/');
    
        $supplier = new Supplier('Reisser', $company);
        $supplier->setWebShopLogin('SG');
        $supplier->setwebShopPassword('qWZ97JLkIQ9iZg9cHOiOC7eXAA+TSH0ycGw6vQShQyQgr1kp8hEmGGKPKuT7TLheYOAyK8cVoA==');
        $supplier->setConnectedSupplier($connectedSupplier);
        $supplier->setCustomerNumber('30037530');
    
        $material = new Material("reisser1", 'new reisser', $company);
        $material->setAutoSearchTerm('VZ602/271');
        $material->setAutoSupplier($supplier);
        $material->setAutoStatus(AutoStatus::new());
    
        return $this->crawler->getMaterialDataForOneMaterial($material, $supplier, true);
    }
    
    private function testGetMaterialSR(): AutoMaterialDto
    {
        $company = new Company('command', true);
    
        $connectedSupplier = new ConnectedSupplier('Schmidt Rudersdorf', 'https://schmidt-rudersdorf.shop/');
    
        $supplier = new Supplier('Schmidt Rudersdorf', $company);
        $supplier->setWebShopLogin('kontakt@lig-pro.de');
        $supplier->setwebShopPassword('JSPjyyD+4XiDXTR2HgIgHncdrnFb+VCBo1RQPpjkgwcqAGQaTlmVb0ebFSbLMzTU9W/iMSyujq39VwQAeg==');
        $supplier->setConnectedSupplier($connectedSupplier);
    
        $material = new Material("schmidtRu1", 'new Schmidt Rudersdorf', $company);
        $material->setAutoSearchTerm('76110318');
        $material->setAutoSupplier($supplier);
        $material->setAutoStatus(AutoStatus::new());
    
        return $this->crawler->getMaterialDataForOneMaterial($material, $supplier, true);
    }
    
    private function testGetMaterialLotter(): AutoMaterialDto
    {
        $company = new Company('command', true);
    
        $connectedSupplier = new ConnectedSupplier('Lotter', 'https://www.lotter24.de/start');
        
        $supplier = new Supplier('Lotter', $company);
        $supplier->setWebShopLogin('steffen grell');
        $supplier->setWebShopPassword('sRTmUriTTf7FzyYtGekh1ZoGytvP3d8+jJwcD5yYOVakRo4B+IcttqvSFgTyAc912G4Z7kb9/sUZdME=');
        $supplier->setConnectedSupplier($connectedSupplier);
    
        $material = new Material("lotter1", 'new lotter', $company);
        $material->setAutoSearchTerm('G622271');
        $material->setAutoSupplier($supplier);
        $material->setAutoStatus(AutoStatus::new());
    
        return $this->crawler->getMaterialDataForOneMaterial($material, $supplier, true);
    }
    
    public function testGetMaterialData(string $supplierName): AutoMaterialDto
    {
        switch ($supplierName) {
            case 'GC':
                return $this->testGetMaterialGC();
            case 'PM':
                return $this->testGetMaterialPM();
            case 'Reisser':
                return $this->testGetMaterialReisser();
            case 'SR':
                return $this->testGetMaterialSR();
            case 'Lotter':
                return $this->testGetMaterialLotter();
        }
    
        throw InconsistentDataException::forAutoSupplierMissing($supplierName);
    }
    
    private function testGetAvailabilityInfosGC(): AvailabilityInfosDto
    {
        $company = new Company('command', true);
    
        $connectedSupplier = new ConnectedSupplier('GC', 'https://gconlineplus.de/');
    
        $supplier = new Supplier('GC', $company);
        $supplier->setWebShopLogin('LIGLagerbestellung');
        $supplier->setwebShopPassword('JoNp9ZOzIep6dmNB8NfbGacXdQ5RfNTRxQo+urqXJELoXg9sbd3To7F4IjIArPKaveU9MvYpQxhbnuOXvohWS/JR');
        $supplier->setConnectedSupplier($connectedSupplier);
    
        $material = new Material("pm1", 'new pm', $company);
        
        $orderSource = new OrderSource('ev', 1, $material, $supplier, $company);
        
        return $this->crawler->getAvailabilityInfosForOneOrderSource($orderSource, true);
    }
    
    private function testGetAvailabilityInfosPM(): AvailabilityInfosDto
    {
        $company = new Company('command', true);
    
        $connectedSupplier = new ConnectedSupplier('PFEIFFER & MAY', 'https://online.pfeiffer-may.de:8443');
    
        $supplier = new Supplier('PFEIFFER & MAY', $company);
        $supplier->setWebShopLogin('kontakt@steffengrell.de');
        $supplier->setwebShopPassword('cEsuHodPHfEbtiTdr7sh+KWE/yzf+gTB6qfNTZGWIRxaFBWYyLUTVSDSXHNKAFiy7MIbZmLZ0Q==');
        $supplier->setConnectedSupplier($connectedSupplier);
    
        $material = new Material("pm1", 'new pm', $company);
    
        $orderSource = new OrderSource('MEW20K', 1, $material, $supplier, $company);
        
        return $this->crawler->getAvailabilityInfosForOneOrderSource($orderSource, true);
    }
    
    private function testGetAvailabilityInfosReisser(): AvailabilityInfosDto
    {
        $company = new Company('command', true);
    
        $connectedSupplier = new ConnectedSupplier('Reisser', 'https://reisser.sct.de/');
    
        $supplier = new Supplier('Reisser', $company);
        $supplier->setWebShopLogin('SG');
        $supplier->setwebShopPassword('qWZ97JLkIQ9iZg9cHOiOC7eXAA+TSH0ycGw6vQShQyQgr1kp8hEmGGKPKuT7TLheYOAyK8cVoA==');
        $supplier->setConnectedSupplier($connectedSupplier);
        $supplier->setCustomerNumber('30037530');
    
        $material = new Material("reisser1", 'new reisser', $company);
    
        $orderSource = new OrderSource('VZ602/271', 1, $material, $supplier, $company);
        
        return $this->crawler->getAvailabilityInfosForOneOrderSource($orderSource, true);
    }
    
    private function testGetAvailabilityInfosSR(): AvailabilityInfosDto
    {
        $company = new Company('command', true);
    
        $connectedSupplier = new ConnectedSupplier('Schmidt Rudersdorf', 'https://schmidt-rudersdorf.shop/');
    
        $supplier = new Supplier('Schmidt Rudersdorf', $company);
        $supplier->setWebShopLogin('kontakt@lig-pro.de');
        $supplier->setwebShopPassword('JSPjyyD+4XiDXTR2HgIgHncdrnFb+VCBo1RQPpjkgwcqAGQaTlmVb0ebFSbLMzTU9W/iMSyujq39VwQAeg==');
        $supplier->setConnectedSupplier($connectedSupplier);
    
        $material = new Material("schmidtRu1", 'new Schmidt Rudersdorf', $company);
    
        $orderSource = new OrderSource('76110318', 1, $material, $supplier, $company);
        
        return $this->crawler->getAvailabilityInfosForOneOrderSource($orderSource, true);
    }
    
    private function testGetAvailabilityInfosLotter(): AvailabilityInfosDto
    {
        $company = new Company('command', true);
    
        $connectedSupplier = new ConnectedSupplier('Lotter', 'https://www.lotter24.de/start');
    
        $supplier = new Supplier('Lotter', $company);
        $supplier->setWebShopLogin('steffen grell');
        $supplier->setWebShopPassword('sRTmUriTTf7FzyYtGekh1ZoGytvP3d8+jJwcD5yYOVakRo4B+IcttqvSFgTyAc912G4Z7kb9/sUZdME=');
        $supplier->setConnectedSupplier($connectedSupplier);
    
        $material = new Material("lotter1", 'new lotter', $company);
    
        $orderSource = new OrderSource('329015', 1, $material, $supplier, $company);
        
        return $this->crawler->getAvailabilityInfosForOneOrderSource($orderSource, true);
    }
    
    public function testGetAvailabilityInfos(string $supplierName): AvailabilityInfosDto
    {
        switch ($supplierName) {
            case 'GC':
                return $this->testGetAvailabilityInfosGC();
            case 'PM':
                return $this->testGetAvailabilityInfosPM();
            case 'Reisser':
                return $this->testGetAvailabilityInfosReisser();
            case 'SR':
                return $this->testGetAvailabilityInfosSR();
            case 'Lotter':
                return $this->testGetAvailabilityInfosLotter();
        }
        
        throw InconsistentDataException::forAutoSupplierMissing($supplierName);
    }
}
