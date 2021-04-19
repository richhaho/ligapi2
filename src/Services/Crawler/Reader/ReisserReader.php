<?php

declare(strict_types=1);

namespace App\Services\Crawler\Reader;

use App\Api\Dto\AutoMaterialDto;
use App\Api\Dto\OrderSourceDto;
use App\Api\Dto\SupplierDto;
use App\Entity\Company;
use App\Exceptions\Crawler\CrawlerScenarioException;
use App\Exceptions\Crawler\SelectorNotFoundException;
use App\Services\Crawler\Dto\AvailabilityInfosDto;
use App\Services\Crawler\ReaderInterface;
use Exception;
use Facebook\WebDriver\WebDriverDimension;
use Money\Money;
use Throwable;

class ReisserReader extends PantherReader implements ReaderInterface
{
    private const SUPPLIER_NAME = 'Reisser';

    public function login(string $username, string $password, string $customerNumber): void
    {
        $this->client->request('GET', 'https://reisser.sct.de/');
    
        $this->client->manage()->window()->setSize(new WebDriverDimension(1920, 1280));

//        try {
//            $this->elementExists('#pdsModal', 'Cookie pop-up exists');
//            $this->click('#submitSelected', 'Close cookie pop-up');
//        } catch (Throwable $t) {
//            // that's okay, sometimes cookie pop-up is missing
//        }

//        $this->click('#link-login', 'Open login dialog', '#link-login', false, 1000);

        $this->enterText('#KDNR', $customerNumber, 'Enter user name');
        $this->enterText('#USER', $username, 'Enter customer number');
        $this->enterText('#PASSWD', $password, 'Enter password');

        $this->click('#GHFSID_628', 'Submit login');

        $this->loggedIn('login end');
    }

    public function logout(): void
    {
//        $this->client->request('GET', 'https://reisser.sct.de/');

        $this->loggedIn('logout start');

        $this->click('[name="img41362"]', 'Log out');
        
        $this->click('[name="fsimg1169"]', 'Log out');
    }

    public function readAvailabilityInfosForSearchTerm(string $searchTerm): AvailabilityInfosDto
    {
        $this->loggedIn('read prices start');

        $this->enterText('#DivSuchfeldNew', $searchTerm, 'Pasting query in the search field');

        $this->click('[src="/reisserbilder/images/IconSuche35px.jpg"]', 'Clicking on search icon');
    
        try {
            $this->getElement('//div[contains(., "ARTIKELINFORMATION")]', 'See, if detail page is already active', null, true);
        } catch (Exception $e) {
            $this->click('#td_tr_1', 'Open first result');
        }
    
        $priceString = $this->getTextFromElement('#nettopreis', 'Get net price');
    
        $price = $this->getMoneyFromPrice($priceString);
        
        $availability1_location = $this->getTextFromElement('//*[@class="lagersortierung"]/tbody/tr[2]/td[1]', 'Get first availability table item', null, true);
        $availability1_stock = $this->getTextFromElement('//*[@class="lagersortierung"]/tbody/tr[2]/td[2]', 'Get first availability table item', null, true);
        $availability2_location = $this->getTextFromElement('//*[@class="lagersortierung"]/tbody/tr[3]/td[1]', 'Get first availability table item', null, true);
        $availability2_stock = $this->getTextFromElement('//*[@class="lagersortierung"]/tbody/tr[3]/td[2]', 'Get first availability table item', null, true);
    
        $availabilityString = $availability1_location . ': ' . $availability1_stock . ', ' . $availability2_location . ': ' . $availability2_stock;
        
        return new AvailabilityInfosDto($price, $availabilityString);
    }

    public function readAllDetails(string $searchTerm, Company $company): AutoMaterialDto
    {
        $materialCrawlerDto = new AutoMaterialDto();
        $materialCrawlerDto->orderSources = [new OrderSourceDto()];
        $materialCrawlerDto->orderSources[0]->supplier = new SupplierDto();
    
        $this->enterText('#DivSuchfeldNew', $searchTerm, 'Pasting query in the search field');
    
        $this->click('[src="/reisserbilder/images/IconSuche35px.jpg"]', 'Clicking on search icon');
    
        try {
            $this->getElement('//div[contains(., "ARTIKELINFORMATION")]', 'See, if detail page is already active', null, true);
        } catch (Exception $e) {
            $this->click('#td_tr_1 td', 'Open first result');
        }
        
        $priceString = $this->getTextFromElement('#nettopreis', 'Get net price');
        
        $price = $this->getMoneyFromPrice($priceString);

        $materialCrawlerDto->orderSources[0]->price = ((int) $price->getAmount()) / 100;
        
        $amountPerPurchaseUnitString = $this->getTextFromElement('//td[text()[contains(.,"Artikel-Preis")]]/following-sibling::td/b', 'Get purchase unit', null, true);
        $materialCrawlerDto->orderSources[0]->amountPerPurchaseUnit = (float) $this->getAmountPerPurchaseUnitFromString($amountPerPurchaseUnitString);
    
        $bruttoPriceString = $this->getTextFromElement('//*[@id="nettopreis"]/../../../../td[1]', 'Get net price', null, true);
        $materialCrawlerDto->sellingPrice = ((int) $this->getMoneyFromPrice($bruttoPriceString)->getAmount()) / 100;

        $fullTitle = $this->getTextFromElement('/html/body/form/div/div[14]/table/tbody/tr[3]/td', 'Getting name', null, true);
        $materialCrawlerDto->name = $this->sanitize($fullTitle);

        $sku = $this->getTextFromElement('//td[text()[contains(.,"Artikelnummer")]]/following-sibling::td/b', 'Getting the sku', null, true);
        if ($sku) {
            $materialCrawlerDto->orderSources[0]->orderNumber = $sku;
        }

        $materialCrawlerDto->imgFile = $this->getFileFromImage('#sct_gallery_target_0 img');
        
        $materialCrawlerDto->unit = $this->getAmountPerPurchaseUnitFromString($amountPerPurchaseUnitString, 1);

//        $materialCrawlerDto->manufacturerNumber = '?'; // didn't find it on the page
//        $materialCrawlerDto->manufacturerName = '?'; // didn't find it on the page

//        try {
//            $materialCrawlerDto->note = $this->sanitize(
//                $this->getTextFromElement('#collapse1 .productDescriptionText', 'Getting details', null, false, 0, false, true)
//            );
//        } catch (Exception $e) {
//            $materialCrawlerDto->note = '';
//        }

        return $materialCrawlerDto;
    }

    public function orderMaterial(string $orderNumber, float $amount, ?float $expectedPrice): string
    {
        $this->loggedIn('orderMaterial Top');

        $this->enterText('#search', $orderNumber, 'Pasting the query in the search field');

        $this->click('#header-butt-search', 'Looking for the query');

        $firstProductSelector = '#resultsList .productListItem:first-of-type .mls .productName';

        try { // TODO: make sure that it is the correct order number
            $this->click($firstProductSelector, 'Pick the first result', $firstProductSelector, false, 1000);
        } catch (Throwable $t) {
            throw CrawlerScenarioException::forProductNotFound($firstProductSelector);
        }

        $this->enterText('#qtyInput', (string) $amount, 'Enter amount', '#qtyInput', false, 1000);

        $this->click('#addToCartButton', 'Add to the cart');

        $result = $this->getElement('#pdsModal .modal-header', 'Waiting for the pop-up', '#pdsModal .modal-header', false, 5)
            ->getText();

        if ($result !== 'Ihrem Warenkorb erfolgreich hinzugefügt') {
            return $result;
        }

        $this->click('a[data-id="data-link-cart"]', 'Click on the cart', 'a[data-id="data-link-cart"]', false, 2000);

        $this->click('#checkoutButton', 'Submit the cart', '#checkoutButton', false, 1000);

        $this->click('#deliveryAddressId_delivery_chosen', 'Open list of addresses', '#deliveryAddressId_delivery_chosen', false, 1000);

        $this->click('.active-result[data-option-array-index="1"]', 'Select a first address');

        $this->click('#checkoutConfigSubmitBottom', 'Submit cart settings');

        return 'success';
    }
    
    public function quit(): void
    {
        $this->client->quit();
    }

    public function getName(): string
    {
        return self::SUPPLIER_NAME;
    }

    protected function loggedIn(string $id): void
    {
        try {
            $this->elementExists('[name="img41362"]', 'We are logged in: ' . $id);
        } catch (SelectorNotFoundException $exception) {
            throw CrawlerScenarioException::forNotLoggedIn('[name="img41362"]');
        }
    }

    protected function getMoneyFromPrice(string $price): Money
    {
        $price = explode(' ', $price)[0];
        $price = str_replace(',', '', $price); // eurocents
        $price = ltrim($price, '0'); // remove leading zeros
        return Money::EUR($price);
    }

    protected function getAmountPerPurchaseUnitFromString(string $str, int $count = 0)
    {
        $str = ltrim($str);
        return explode(' ', $str)[$count];
    }

    protected function sanitize(string $string): string
    {
        return trim(preg_replace('/\s+/', ' ', $string));
    }
}
