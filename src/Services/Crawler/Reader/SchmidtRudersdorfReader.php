<?php

declare(strict_types=1);

namespace App\Services\Crawler\Reader;

use App\Api\Dto\AutoMaterialDto;
use App\Api\Dto\OrderSourceDto;
use App\Api\Dto\SupplierDto;
use App\Entity\Company;
use App\Exceptions\Crawler\CrawlerOtherException;
use App\Exceptions\Crawler\CrawlerScenarioException;
use App\Exceptions\Crawler\SelectorNotFoundException;
use App\Services\Crawler\Dto\AvailabilityInfosDto;
use App\Services\Crawler\ReaderInterface;
use Exception;
use Facebook\WebDriver\WebDriverDimension;
use Money\Money;
use Throwable;

class SchmidtRudersdorfReader extends PantherReader implements ReaderInterface
{
    private const SUPPLIER_NAME = 'Schmidt Rudersdorf';

    public function login(string $username, string $password, ?string $customerNumber = null): void
    {
        $this->client->request('GET', 'https://schmidt-rudersdorf.shop/');
    
        $this->client->manage()->window()->setSize(new WebDriverDimension(1920, 1280));

        try {
            $this->elementExists('#submitSelected', 'Cookie pop-up exists', null, null, 1000);
            $this->click('#submitSelected', 'Close cookie pop-up');
        } catch (Throwable $t) {
            // that's okay, sometimes cookie pop-up is missing
        }

        $this->click('#link-login', 'Open login dialog', '#link-login', false, 1000);

        $this->enterText('#j_usernamePopup', $username, 'Enter the login', '#j_usernamePopup', false, 1000);
        $this->enterText('#j_passwordPopup', $password, 'Enter the password');

        $this->click('#loginForm button', 'Submit the login');

        $this->loggedIn();
    }

    public function logout(): void
    {
        $this->client->request('GET', 'https://schmidt-rudersdorf.shop/');

        $this->loggedIn();

        $this->click('#link-logout', 'Log out', '#link-logout', false, 1000);

        try {
            $this->click('button[data-id="butt-logout"]', 'Submit log out', 'button[data-id="butt-logout"]', false, 1000);
        } catch (Throwable $t) {
            // that's okay, sometimes logout pop-up is missing
        }
    }

    public function readAvailabilityInfosForSearchTerm(string $searchTerm): AvailabilityInfosDto
    {
        $this->loggedIn();

        $this->enterText('#search', $searchTerm, 'Pasting the query in the search field');

        $this->click('#header-butt-search', 'Looking for the query');

        $firstProductSelector = '#resultsList .productListItem:first-of-type .mls .productName';

        try { // TODO: make sure that it is the correct order number
            $this->click($firstProductSelector, 'Pick the first result', $firstProductSelector, false, 1000);
        } catch (Throwable $t) {
            throw CrawlerScenarioException::forProductNotFound($firstProductSelector);
        }

        $price = $this->getTextFromElement('#base-price', 'Getting the price', '#base-price', false, 1000);

        if ($price === '') {
            $price = $this->getTextFromElement('#js-big-price', 'Trying to get price from primary field', '#js-big-price', false, 1000);
        }

        if ($price === '') {
            throw CrawlerOtherException::forOtherException('price is empty', '#js-big-price', '');
        }

        $availability = $this->getTextFromElement('#liveStock', 'getting availability');

        return new AvailabilityInfosDto($this->getMoneyFromPrice($price), $this->sanitize($availability));
    }

    public function readAllDetails(string $searchTerm, Company $company): AutoMaterialDto
    {
        $materialCrawlerDto = new AutoMaterialDto();
        $materialCrawlerDto->orderSources = [new OrderSourceDto()];
        $materialCrawlerDto->orderSources[0]->supplier = new SupplierDto();
    
        $availabilityInfosDto = $this->readAvailabilityInfosForSearchTerm($searchTerm); // We also open pop-up with an item

        $materialCrawlerDto->orderSources[0]->price = ((int) $availabilityInfosDto->getPrice()->getAmount()) / 100;
        $materialCrawlerDto->orderSources[0]->amountPerPurchaseUnit = 1; // didn't find it on the page

        $oldPrice = $this->getTextFromElement('#old-price', 'Getting the old price');
        $materialCrawlerDto->sellingPrice = ((int) $this->getMoneyFromPrice($oldPrice)->getAmount()) / 100;

        $fullTitle = $this->getTextFromElement('span.product-name.mbm', 'Getting the full title', null, false, 0, true);
        if ($fullTitle) {
            $materialCrawlerDto->name = $this->sanitize($fullTitle);
        }

        $sku = $this->getTextFromElement('.product-code.mbm span[itemprop]', 'Getting the sku');
        if ($sku) {
            $materialCrawlerDto->orderSources[0]->orderNumber = $sku;
        }

        $materialCrawlerDto->imgFile = $this->getFileFromImage('.main-product-image', 'https://schmidt-rudersdorf.shop');

        try {
            $materialCrawlerDto->unit = $this->getTextFromElement('.unitInput option[selected]', 'Getting the unit');
        } catch (Throwable $throwable) {
            $materialCrawlerDto->unit = $this->getTextFromElement('.unitSingle .mlm', 'Getting the unit from secondary field');
        }
    
        $materialCrawlerDto->unit = $this->sanitize($materialCrawlerDto->unit);

//        $materialCrawlerDto->manufacturerNumber = '?'; // didn't find it on the page
//        $materialCrawlerDto->manufacturerName = '?'; // didn't find it on the page

        try {
            $materialCrawlerDto->note = $this->sanitize(
                $this->getTextFromElement('.productDescriptionText', 'Getting details')
            );
        } catch (Exception $e) {
            $materialCrawlerDto->note = '';
        }

        return $materialCrawlerDto;
    }

    public function orderMaterial(string $orderNumber, float $amount, ?float $expectedPrice): string
    {
        $this->loggedIn();

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

    protected function loggedIn(): void
    {
        try {
            $this->elementExists('#link-logout', 'We are logged in');
        } catch (SelectorNotFoundException $exception) {
            throw CrawlerScenarioException::forNotLoggedIn('#link-logout');
        }
    }

    protected function getMoneyFromPrice(string $price): Money
    {
        $price = explode(' ', $price)[0];
        $price = str_replace(',', '', $price); // eurocents
        $price = ltrim($price, '0'); // remove leading zeros
        return Money::EUR($price);
    }

    protected function sanitize(?string $string): string
    {
        if (!$string) {
            return '';
        }
        $string = trim(preg_replace('/\s+/', ' ', $string));

        $string = str_replace('\n', ' ', $string);

        return trim(preg_replace('/\s+/', ' ', $string));
    }
}
