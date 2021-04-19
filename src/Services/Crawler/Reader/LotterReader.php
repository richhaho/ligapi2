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
use Facebook\WebDriver\WebDriverDimension;
use Money\Money;
use Throwable;

class LotterReader extends PantherReader implements ReaderInterface
{
    private const SUPPLIER_NAME = 'Lotter';

    public function login(string $username, string $password, ?string $customerNumber = null): void
    {
        $this->client->request('GET', 'https://www.lotter24.de/start');
    
        $this->client->manage()->window()->setSize(new WebDriverDimension(1920, 1280));

        $this->enterText('#login-page-form input[placeholder="Benutzername"]', $username, 'Enter the login');
        $this->enterText('#login-page-form input[placeholder="Passwort"]', $password, 'Enter the password');

        $this->click('#login-page-form button', 'Submit the login');

        $this->loggedIn();
    }

    public function logout(): void
    {
        $this->client->request('GET', 'https://www.lotter24.de/start');

        $this->loggedIn();

        $this->click('.benefit a', 'Log out');
    }

    public function readAvailabilityInfosForSearchTerm(string $searchTerm): AvailabilityInfosDto
    {
        $this->loggedIn();

        $this->enterText('#autocomplete-input-tokenfield', $searchTerm, 'Pasting the query in the search field');

        $this->click('.btn-search', 'Looking for the query');

        $firstItemSelector = '.item-line:first-of-type .item-info a';

        try {
            $this->click($firstItemSelector, 'Pick first item', $firstItemSelector, false, 1000);
        } catch (Throwable $t) {
            throw CrawlerScenarioException::forProductNotFound($firstItemSelector);
        }
    
        $availability = $this->getTextFromElement('.item-availability span', 'getting availability');

        $price = $this->getTextFromElement('.item-price-lg span[class=""]', 'Getting the price');
    
        return new AvailabilityInfosDto($this->getMoneyFromPrice($price), $availability);
    }

    public function readAllDetails(string $searchTerm, Company $company): AutoMaterialDto
    {
        $materialCrawlerDto = new AutoMaterialDto();
        $materialCrawlerDto->orderSources = [new OrderSourceDto()];
        $materialCrawlerDto->orderSources[0]->supplier = new SupplierDto();
        
        $availabilityInfosDto = $this->readAvailabilityInfosForSearchTerm($searchTerm); // we opened product's page here
    
        $materialCrawlerDto->orderSources[0]->price = ((int) $availabilityInfosDto->getPrice()->getAmount()) / 100;
        $materialCrawlerDto->orderSources[0]->amountPerPurchaseUnit = 1; // didn't find it on the page

        $oldPrice = $this->getTextFromElement('.item-details-main .item-price-recommended', 'Getting the old price');
        $materialCrawlerDto->sellingPrice = ((int) $this->getMoneyFromPrice($oldPrice)->getAmount()) / 100;

        $fullTitle = $this->getTextFromElement('span.item-head-short-text', 'Getting the full title');
        $materialCrawlerDto->name = $this->sanitize($fullTitle);

        $sku = $this->getTextFromElement('.item-head .item-head-num small', 'Getting the sku', null, false, 0, false, true);
        $sku = str_replace('Artikelnummer:', '', $sku);
        $materialCrawlerDto->orderSources[0]->orderNumber = $this->sanitize($sku);

        $materialCrawlerDto->imgFile = $this->getFileFromImage('.item-image-js img');

        $materialCrawlerDto->unit = $this->sanitize(
            $this->getTextFromElement('.item-details-main .item-price-quantity', 'Getting the unit')
        );
        
        $noteElement = $this->getTextFromElement('.oxomi-longtext', 'Getting note elements');
        
        $materialCrawlerDto->note = $this->sanitize($noteElement);
        
        return $materialCrawlerDto;
    }

    public function orderMaterial(string $orderNumber, float $amount, ?float $expectedPrice): string
    {
        $this->loggedIn();

        // ...

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
            $this->elementExists('.benefit a', 'We are logged in');
        } catch (SelectorNotFoundException $exception) {
            throw CrawlerScenarioException::forNotLoggedIn('.benefit a');
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
        return trim(preg_replace('/\s+/', ' ', $string));
    }
}
