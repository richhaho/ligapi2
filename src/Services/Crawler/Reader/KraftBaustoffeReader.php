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
use Money\Money;
use Throwable;

class KraftBaustoffeReader extends PantherReader implements ReaderInterface
{
    private const SUPPLIER_NAME = 'KRAFT Baustoffe';

    public function login(string $username, string $password, ?string $customerNumber = null): void
    {
        $this->client->request('GET', 'https://www.kraft-baustoffe.de/shop/b2b/PrivateLogin/index/requireReload/');
        
        $this->click('.popup_text button', 'Go to business login');

        $this->enterText('#email', $username, 'Enter the login');
        $this->enterText('#passwort', $password, 'Enter the password');

        $this->click('.register--login-btn', 'Submit the login');

        try {
            $this->elementExists('#mat-dialog-0 > pm-start-motd > mat-dialog-actions > button', 'Pop-up exists');
            $this->click('#mat-dialog-0 > pm-start-motd > mat-dialog-actions > button', 'Close pop-up');
        } catch (Throwable $t) {
            // that's okay, sometimes pop-up is missing
        }

        $this->loggedIn();
    }

    public function logout(): void
    {
        $this->client->request('GET', 'https://online.pfeiffer-may.de:8443/#/start');

        $this->client->reload();

        $this->loggedIn();

        $this->click('mat-toolbar > mat-toolbar-row > span.toolbar.toolbar-icons > button:nth-child(4) > span > mat-icon', 'Log out', 'mat-icon', false, 1000);

        $buttonElements = $this->getElements('.mat-menu-item', 'Getting buttons', '.mat-menu-item', false, 1000);

        $logoutButton = $buttonElements[count($buttonElements) - 1];

        $logoutButton->click();
    }

    public function readAvailabilityInfosForSearchTerm(string $searchTerm): AvailabilityInfosDto
    {
        $this->loggedIn();

        $this->client->waitFor('pm-start > div > pm-kacheln > div');
        $this->click('pm-start > div > pm-kacheln > div > div:nth-child(3) > mat-card > div > img', 'Click buy now');

        $this->client->waitFor('input[aria-label="artikelsuche"]');
        $this->enterText('#artikelsuche', $searchTerm, 'Paste the query in the search field');

        $this->click('.navbar-search-button', 'Looking for the query');

        $firstProductSelector = '.item-kbn > .item-link';

        try {
            $this->click($firstProductSelector, 'Pick the first result', $firstProductSelector, false, 1000);
        } catch (Throwable $t) {
            throw CrawlerScenarioException::forProductNotFound($firstProductSelector);
        }

        $availability = $this->getTextFromElement('.col-md-7 .lkz.lkz-l.lkz-text', 'getting availability');
    
        return new AvailabilityInfosDto($this->getPrice(), $this->sanitize($availability));
    }

    public function readAllDetails(string $searchTerm, Company $company): AutoMaterialDto
    {
        $materialCrawlerDto = new AutoMaterialDto();
        $materialCrawlerDto->orderSources = [new OrderSourceDto()];
        $materialCrawlerDto->orderSources[0]->supplier = new SupplierDto();
    
        $availabilityInfosDto = $this->readAvailabilityInfosForSearchTerm($searchTerm); // We also open item's page

        $materialCrawlerDto->orderSources[0]->price = ((int) $availabilityInfosDto->getPrice()->getAmount()) / 100;
        $materialCrawlerDto->sellingPrice = ((int)$this->getPrice(false)->getAmount()) / 100;

        $materialCrawlerDto->orderSources[0]->amountPerPurchaseUnit = 1;

        $descriptionFields = $this->getElements('.copyclick', 'Getting description fields', '.copyclick', false, 1000);

        $materialCrawlerDto->orderSources[0]->orderNumber = $this->sanitize($descriptionFields[0]->getText());
        $materialCrawlerDto->name = $this->sanitize($descriptionFields[1]->getText());
        $materialCrawlerDto->unit = $this->sanitize($descriptionFields[5]->getText());

        $additionalInfoFields = $this->getElements('.item-value', 'Getting description fields', '.item-value', false, 1000);

        $manufacturerElement = $additionalInfoFields[count($additionalInfoFields) - 1];

        $materialCrawlerDto->manufacturerName = $this->sanitize($manufacturerElement->getText());

        $materialCrawlerDto->imgFile = $this->getFileFromImage('.no-print.item-gallery-single img', $company);

        return $materialCrawlerDto;
    }

    public function orderMaterial(string $orderNumber, float $amount, ?float $expectedPrice): string
    {
        $this->readAvailabilityInfosForSearchTerm($orderNumber); // We also open item's page

        $quantityInputSelector = '.item-to-cart-details .mat-input-element';

        $this->enterText($quantityInputSelector, (string)$amount, 'Enter amount', $quantityInputSelector, false, 1000);

        $this->click('.item-to-cart-details .item-add-to-cart', 'Add to the cart');

        return 'success';
    }

    public function getName(): string
    {
        return self::SUPPLIER_NAME;
    }
    
    public function quit(): void
    {
        $this->client->quit();
    }

    protected function loggedIn(): void
    {
        $selector = 'pm-navbar > mat-toolbar > mat-toolbar-row:nth-child(1) > span.toolbar.toolbar-icons > button:nth-child(5) > span > mat-icon';

        try {
            $this->elementExists($selector, 'We are logged in');
        } catch (SelectorNotFoundException $exception) {
            throw CrawlerScenarioException::forNotLoggedIn($selector);
        }
    }

    protected function getMoneyFromPrice(string $price): Money
    {
        $price = explode(' ', $price)[0];
        $price = str_replace(',', '', $price); // eurocents
        $price = ltrim($price, '0'); // remove leading zeros

        return Money::EUR($price);
    }

    protected function sanitize(string $string): string
    {
        return trim(preg_replace('/\s+/', ' ', $string));
    }

    protected function getPrice(?bool $primary = true): Money
    {
        $priceElementSelector = 'span.ml-0.ng-star-inserted';

        $elements = $this->getElements($priceElementSelector, 'Getting the price', $priceElementSelector, false, 1000);

        $price = $elements[$primary ? 0 : 1]->getText();

        if ($price === '') {
            throw CrawlerOtherException::forOtherException('Price is empty', $priceElementSelector, '');
        }

        return $this->getMoneyFromPrice($price);
    }
}
