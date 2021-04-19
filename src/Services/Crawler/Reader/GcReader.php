<?php

declare(strict_types=1);


namespace App\Services\Crawler\Reader;


use App\Api\Dto\AutoMaterialDto;
use App\Api\Dto\OrderSourceDto;
use App\Api\Dto\SupplierDto;
use App\Entity\Company;
use App\Exceptions\Crawler\CrawlerOtherException;
use App\Services\Crawler\Dto\AvailabilityInfosDto;
use App\Services\Crawler\ReaderInterface;
use Exception;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverDimension;
use Money\Money;
use Throwable;

class GcReader extends PantherReader implements ReaderInterface
{
    private const SUPPLIER_NAME = 'GC';

    private function getMoneyFromPrice(string $price): Money
    {
        $price = explode(' ', $price)[0];
        $price = str_replace(',', '', $price);
        $price = str_replace('.', '', $price);
        return Money::EUR($price);
    }
    
    private function getStatusOfMaterialOrderItem(string $orderNumber): string
    {
        try {
            $this->client->waitFor('//table[@data-cid="table"]//div[contains(.,"'.$orderNumber.'")]/parent::td/parent::tr//td[@data-column-id="3"]//img[@src="css/images/gruen.png"]', 5);
        } catch (Exception $e) {
            $upperString = strtoupper($orderNumber);
            $locator = '//table[@data-cid="table"]//div[contains(.,"'.$upperString.'")]/parent::td/parent::tr//td[@data-column-id="2"]';
            $this->client->waitFor($locator, 5);
            return $this->client->getCrawler()->findElement(WebDriverBy::xpath($locator))->getText();
        }
        return 'success';
    }
    
    public function login(string $username, string $password, ?string $customerNumber): void
    {
        $this->client->manage()->window()->setSize(new WebDriverDimension(1920, 1280));
        
        $this->client->request('GET', 'https://gconlineplus.de/');
    
        $crawler = $this->client->waitFor('#a1_inputName');
        
    
        $form = $crawler->selectButton('Login')->form();
    
        try {
            $form['a1_inputName'] = $username;
            $form['a1_inputPass'] = $password;
            $crawler->selectButton('Login')->click();
        } catch (Exception $e) {
            $this->client->takeScreenshot($this->publicPath . '/pantherDebug/gc' . '.jpg');
            echo $e->getMessage();
        }
    
        $this->client->waitFor('[data-cid=infoCenterNewsList]');
    }
    
    public function logout(): void
    {
        $this->client->getCrawler()->findElement(WebDriverBy::id('a0_btnLogout'))->click();
        $this->client->waitFor('#a1_btnSubmit');
    }
    
    public function readAllDetails(string $searchTerm, Company $company): AutoMaterialDto
    {
        $materialCrawlerDto = new AutoMaterialDto();
        $materialCrawlerDto->orderSources = [new OrderSourceDto()];
        $materialCrawlerDto->orderSources[0]->supplier = new SupplierDto();
        
        $this->enterText('#a3_searchForm_searchGrid_searchInput', $searchTerm, 'Enter into search input ' . $searchTerm);
        $this->click('#a3_searchForm_searchGrid_searchInput_btnInSearch', 'Click on search button', null, null, 500);
        $this->click('[data-id="0"] [data-column-id="1"]', 'click on first result');
        
        $purchasingPriceString = $this->getTextFromElement('//div[@data-cid="NetPrice"][contains(.,"EUR")][not(contains(.,"0,00"))]', 'Get purchasing price', null, true, 300);
        $materialCrawlerDto->orderSources[0]->price = ((int) $this->getMoneyFromPrice($purchasingPriceString)->getAmount()) / 100;
        
        $amountPerPurchaseUnitString = $this->getTextFromElement('//div[contains(text(),"Preiseinheit")]/parent::*/following-sibling::div', 'Hersteller Nummer lesen', null, true);
        $materialCrawlerDto->orderSources[0]->amountPerPurchaseUnit = (float) explode(' ', $amountPerPurchaseUnitString)[1];
        
        $sellingPriceString = $this->getTextFromElement('[data-cid="priceGrid"] .ui-block-b', 'Get selling price');
        $materialCrawlerDto->sellingPrice = ((int) $this->getMoneyFromPrice($sellingPriceString)->getAmount()) / 100;
        
        $fullTitle = $this->getTextFromElement('[data-cid="Title"]', 'Get Name');
        $fullTitle = preg_replace( "/\n/", "|||", $fullTitle );
        
        $materialCrawlerDto->name = explode('|||', $fullTitle)[1];
        
        $materialCrawlerDto->orderSources[0]->orderNumber = explode('|||', $fullTitle)[0];
        
        $materialCrawlerDto->imgFile = $this->getFileFromImage('[data-cid="largeImage-0"] img');
        
        $materialCrawlerDto->unit = $this->getTextFromElement('//div[contains(text(),"Mengeneinheit")]/parent::*/following-sibling::div', 'Hersteller Nummer lesen', null, true);
    
        try {
            $materialCrawlerDto->manufacturerNumber = $this->getTextFromElement('//div[contains(text(),"Hersteller Artikelnummer")]/parent::*/following-sibling::div', 'Hersteller Nummer lesen', null, true);
        } catch (Throwable $e) {
            $materialCrawlerDto->manufacturerNumber = '';
        }
    
        try {
            $materialCrawlerDto->manufacturerName = $this->getTextFromElement('//span[contains(text(),"Herstellername")]/parent::span/following-sibling::span', 'Hersteller Name lesen', null, true);
        } catch (Throwable $e) {
            $materialCrawlerDto->manufacturerName = '';
        }
    
        try {
            $materialCrawlerDto->note = $this->getTextFromElement('//h3[contains(@title, "Langtext")]/following-sibling::div', 'Langtext lesen', null, true, null, true);
        } catch (Throwable $e) {
            $materialCrawlerDto->note = '';
        }
        
        $this->click('#popupContainerCloseButton2', 'close metarial details');
        
        return $materialCrawlerDto;
    }
    
    public function readAvailabilityInfosForSearchTerm(string $searchTerm): AvailabilityInfosDto
    {
        $this->enterText('#a3_searchForm_searchGrid_searchInput', $searchTerm, 'Enter into search input ' . $searchTerm);
        $this->click('#a3_searchForm_searchGrid_searchInput_btnInSearch', 'Click on search button', null, null, 1000);
        try {
            $this->click('#PageOnlinePlusProductDetailsContent [data-id="0"] [data-column-id="1"]', 'click on first result');
        } catch (Throwable $e) {
            $this->click('#PageOnlinePlusSearchEsResult [data-id="0"] [data-column-id="1"]', 'click on first result');
        }
        
        $price = $this->getTextFromElement('//div[@data-cid="NetPrice"][contains(.,"EUR")][not(contains(.,"0,00"))]', 'Get price', null, true, 3000);
        
        if ($price === "") {
            throw CrawlerOtherException::forOtherException('price is empty', '//div[@data-cid="NetPrice"][contains(.,"EUR")][not(contains(.,"0,00"))]', '');
        }

        $availability = $this->getTextFromElement('[data-cid="buStockInfo"]', 'getting availability', null, false, 0, false, false, 'datatooltip');
    
        $this->click('#popupContainerCloseButton2', 'close metarial details');

        return new AvailabilityInfosDto($this->getMoneyFromPrice($price), $this->sanitize($availability));
    }
    
    public function orderMaterial(string $orderNumber, float $amount, ?float $expectedPrice): string
    {
        $this->click('//span[contains(.,"Schnellerfassung")]', 'Click on Schnellerfassung', null, true, 300);
    
        $this->enterText('[data-cid=productNumber]', $orderNumber, 'Enter order number search field', null, null, 1000);
        $this->enterText('[data-cid=productCount]', (string) $amount, 'Enter amount');
    
        $this->click('[data-cid=btnProductSubmit]', 'Click on "Position hinzufügen"');
        
        return $this->getStatusOfMaterialOrderItem($orderNumber);
    }
    
    public function quit(): void
    {
        $this->client->quit();
    }

    public function getName(): string
    {
        return self::SUPPLIER_NAME;
    }

    protected function sanitize(string $string): string
    {
        $string = trim(preg_replace('/\s+/', ' ', $string));

        $string = str_replace('<br />', ' ', $string);

        return $string;
    }
}
