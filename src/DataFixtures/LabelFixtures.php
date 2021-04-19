<?php

declare(strict_types=1);


namespace App\DataFixtures;


use App\Entity\Company;
use App\Entity\Data\EntityType;
use App\Entity\PdfDocumentType;
use App\Entity\User;
use App\Services\Pdf\DefaultPdfSpecifications\PdfSpecificationInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LabelFixtures extends Fixture implements DependentFixtureInterface
{
    private iterable $pdfSpecifications;
    
    public function __construct(iterable $pdfSpecifications)
    {
        $this->pdfSpecifications = $pdfSpecifications;
    }
    
    public function getDependencies(): array
    {
        return [
            AppFixtures::class,
        ];
    }
    
    public function load(ObjectManager $manager)
    {
        $company = $manager->getRepository(Company::class)
            ->findOneBy(['name' => 'Lager im Griff']);
        
        $user = $manager->getRepository(User::class)
            ->findOneBy(['email' => 'steffen.grell@lagerimgriff.de']);
        
        /** @var PdfSpecificationInterface $pdfSpecification */
        foreach ($this->pdfSpecifications as $pdfSpecification) {
            $pdfSpec = $pdfSpecification->getPdfSpecification();
            $manager->persist($pdfSpec);
            if ($pdfSpec->getName() === 'A4 98.4 x 38.1') {
                $label1 = new PdfDocumentType(
                    $company,
                    "Standard",
                    EntityType::material(),
                    $pdfSpec
                );
                $manager->persist($label1);
                $user->setSelectedMaterialLabelType($label1);
            }
            if ($pdfSpec->getName() === 'Einzeln 51 x 19') {
                $label2 = new PdfDocumentType(
                    $company,
                    "Klein",
                    EntityType::material(),
                    $pdfSpec
                );
    
                $manager->persist($label2);
            }
            
            if ($pdfSpec->getName() === 'Order') {
                $commonFieldsOrder = $pdfSpec->getDefaultCommonPdfFields();
    
                $commonFieldsOrder[3]['params'] = "Kommission:";
                $commonFieldsOrder[5]['params'] = "Sachbearbeiter:";
                $commonFieldsOrder[7]['params'] = "Datum:";
                $commonFieldsOrder[10]['params'] = "Sehr geehrte Damen und Herren,";
                $commonFieldsOrder[11]['params'] = "wir bestellen zum nächstmöglichen Zeitpunkt folgende Artikel:";
                $commonFieldsOrder[12]['params'] = "Pos";
                $commonFieldsOrder[13]['params'] = "Name";
                $commonFieldsOrder[14]['params'] = "Bestellnummer";
                $commonFieldsOrder[15]['params'] = "Menge";
                $commonFieldsOrder[16]['params'] = "Einheit";
                $commonFieldsOrder[17]['params'] = "Mit freundlichen Grüßen,";
    
                $materialOrder = new PdfDocumentType(
                    $company,
                    'Material Bestellung',
                    EntityType::order(),
                    $pdfSpec,
                    null,
                    $commonFieldsOrder
                );
                $manager->persist($materialOrder);
            }
    
            if ($pdfSpec->getName() === 'Consignment') {
                $commonFieldsConsignment = $pdfSpec->getDefaultCommonPdfFields();
    
                $commonFieldsConsignment[4]['params'] = "KOMMISSIONIERSCHEIN";
                $commonFieldsConsignment[7]['params'] = "Auftragsinhalt:";
                $commonFieldsConsignment[9]['params'] = "Zeit Kommissionierung/Mängel/Doku";
                $commonFieldsConsignment[10]['params'] = "Datum:";
                $commonFieldsConsignment[11]['params'] = "von - bis:";
                $commonFieldsConsignment[12]['params'] = "Pause";
                $commonFieldsConsignment[13]['params'] = "Bezeichnung";
                $commonFieldsConsignment[14]['params'] = "Barcode";
                $commonFieldsConsignment[15]['params'] = "Menge";
                $commonFieldsConsignment[16]['params'] = "Lagerplatz";
                $commonFieldsConsignment[17]['params'] = "Status";
    
                $consignment = new PdfDocumentType(
                    $company,
                    'Kommissionsschein',
                    EntityType::consignment(),
                    $pdfSpec,
                    null,
                    $commonFieldsConsignment
                );
                $manager->persist($consignment);
            }
    
            if ($pdfSpec->getName() === 'Delivery notice') {
                $commonFieldsConsignment = $pdfSpec->getDefaultCommonPdfFields();
    
                $commonFieldsConsignment[4]['params'] = "LIEFERSCHEIN";
                $commonFieldsConsignment[7]['params'] = "Bezeichnung";
                $commonFieldsConsignment[8]['params'] = "Barcode";
                $commonFieldsConsignment[9]['params'] = "Menge";
    
                $consignment = new PdfDocumentType(
                    $company,
                    'Lieferschein',
                    EntityType::consignment(),
                    $pdfSpec,
                    null,
                    $commonFieldsConsignment
                );
                $manager->persist($consignment);
            }
    
            if ($pdfSpec->getName() === 'Cleaning Sheet') {
                $commonFieldsConsignment = $pdfSpec->getDefaultCommonPdfFields();
    
                $commonFieldsConsignment[4]['params'] = "Reinigungsschein";
                $commonFieldsConsignment[7]['params'] = "Bezeichnung";
                $commonFieldsConsignment[8]['params'] = "Barcode";
                $commonFieldsConsignment[9]['params'] = "Menge";
                $commonFieldsConsignment[10]['params'] = "Gereinigt von";
                $commonFieldsConsignment[11]['params'] = "Datum";
                $commonFieldsConsignment[12]['params'] = "Von";
                $commonFieldsConsignment[13]['params'] = "Bis";
    
                $consignment = new PdfDocumentType(
                    $company,
                    'Reinigungsschein',
                    EntityType::consignment(),
                    $pdfSpec,
                    null,
                    $commonFieldsConsignment
                );
                $manager->persist($consignment);
            }
    
            if ($pdfSpec->getName() === 'Repair Sheet') {
                $commonFieldsConsignment = $pdfSpec->getDefaultCommonPdfFields();
    
                $commonFieldsConsignment[4]['params'] = "Reparaturschein";
    
                $consignment = new PdfDocumentType(
                    $company,
                    'Reparaturschein',
                    EntityType::consignment(),
                    $pdfSpec,
                    null,
                    $commonFieldsConsignment
                );
                $manager->persist($consignment);
            }
        }
        
        $manager->flush();
    }
}
