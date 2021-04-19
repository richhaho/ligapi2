<?php

declare(strict_types=1);


namespace App\Services;


use App\Entity\MaterialOrder;
use App\Exceptions\Domain\InconsistentDataException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class UglService
{
    private CurrentUserProvider $currentUserProvider;
    
    public function __construct(CurrentUserProvider $currentUserProvider)
    {
        $this->currentUserProvider = $currentUserProvider;
    }
    
    public function createUgl(MaterialOrder $materialOrder): string
    {
        $fileSystem = new Filesystem();
        $finder = new Finder();
        
        $currentUser = $this->currentUserProvider->getAuthenticatedUser();
        $company = $currentUser->getCompany();
        $currentUserFullName = $currentUser->getFullName();
        
        $companyFolder = $company->getId();
        
        $uploadFolder = 'companyData/' . $companyFolder . '/ugl/';
        
        $fileSystem->mkdir($uploadFolder);
        
        $todayLong = date("Ymd");
        $todayShort = date("ymd");
        
        $tomorrowLong = date("Ymd", strtotime('tomorrow'));
        
        $uglFilebase = 'A0' . $todayShort;
        $version = 1;
        
        $finder->files()->in($uploadFolder);
        
        foreach ($finder as $file) {
            $fileBase = substr($file->getFilename(), 0, 8);
            if ($fileBase === $uglFilebase) {
                $version++;
            }
        }
        
        $uglExtension = str_pad((string) $version, 3, "0", STR_PAD_LEFT);
        
        $uglFileName = $uglFilebase . '.' . $uglExtension;
        
        $uglFile = $uploadFolder . $uglFileName;
        
        $kopf = "KOP";
        
        if (!$materialOrder->getSupplier()->getCustomerNumber()) {
            throw InconsistentDataException::forDataIsMissing('customerNumber');
        }
        
        $kundennummerDesHandwerkers = str_pad($materialOrder->getSupplier()->getCustomerNumber(), 10, ' ', STR_PAD_RIGHT);
        $lieferantennummerDesHaendlers = str_repeat(' ',10);
        $auftragsart = 'BE';
        $anfragenummer = str_pad($materialOrder->getDeliveryNote(),15, ' ',STR_PAD_RIGHT);
        $kundenauftragstext = str_repeat(' ',50);
        $vorgangsnummer = str_repeat(' ',15);
        $gewuenschtesLieferdaten = $tomorrowLong;
        $waehrungszeichen = 'EUR';
        $versionsKennzeichen = '04.00';
        $sachbearbeiter = str_pad($currentUserFullName,40, ' ',STR_PAD_RIGHT);
        $vorgangsdatum = $todayLong;
        
        $kopfZeile = $kopf . $kundennummerDesHandwerkers . $lieferantennummerDesHaendlers .
            $auftragsart . $anfragenummer . $kundenauftragstext . $vorgangsnummer .
            $gewuenschtesLieferdaten . $waehrungszeichen . $versionsKennzeichen . $sachbearbeiter . $vorgangsdatum;
        
        $kopfZeile = str_pad($kopfZeile,200, ' ',STR_PAD_RIGHT);
        
        $data = $kopfZeile . "\r\n";
        
        $positionNr = 1;
        
        forEach($materialOrder->getMaterialOrderPositions() as $materialOrderPosition) {
            
            $position = 'POA' . str_pad((string) $positionNr,10, '0',STR_PAD_LEFT);
            $positionGh = str_repeat(' ',10);
            $orderNumber = $materialOrderPosition->getOrderSource()->getOrderNumber();
            if (strlen($orderNumber) > 15) {
                $orderNumber = substr($orderNumber, 0, 15);
            }
            $articleNumber = str_pad($orderNumber,15, ' ',STR_PAD_RIGHT);							//!!!
            $formattedAmount = $materialOrderPosition->getAmount() * 1000;							//!!!
            $amount = str_pad((string) $formattedAmount,11, '0',STR_PAD_LEFT);
            
            $line = $position . $positionGh . $articleNumber . $amount;
            
            $line = str_pad($line,200, ' ',STR_PAD_RIGHT) . "\r\n" ;
            
            $data .= $line;
            
            $positionNr++;
        }
        
        $data .= str_pad('END',200, ' ',STR_PAD_RIGHT) . "\r\n" ;
        
        $fileSystem->dumpFile($uglFile, $data);
        
        return $uglFile;
    }
}
