<?php

declare(strict_types=1);


namespace App\Services\Crawler\Reader;


use App\Entity\Supplier;
use App\Exceptions\Crawler\CrawlerOtherException;
use App\Exceptions\Crawler\CrawlerSetupException;
use App\Exceptions\Crawler\CrawlerTimeoutException;
use App\Exceptions\Crawler\SelectorNotFoundException;
use App\Services\Crawler\Downloader;
use App\Services\Crawler\ReaderInterface;
use Exception;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Panther\Client;
use function is_resource;

abstract class PantherReader implements ReaderInterface
{
    private const UNKNOWN_SUPPLIER = 'Unknown supplier';
    private const STANDARD_PORT = 9515;
    private int $currentPort;

    protected Client $client;

    protected string $publicPath;

    private Downloader $downloader;
    private string $crawlerClient;
    
    public function __construct(string $publicPath, Downloader $downloader, string $crawlerClient)
    {
        $this->publicPath = $publicPath;
        $this->downloader = $downloader;
        $this->currentPort = self::STANDARD_PORT;
        $this->setUpClient();
        $this->crawlerClient = $crawlerClient;
    }
    
    private function setUpClient(): void
    {
        if ($this->currentPort === 9525) {
            throw CrawlerSetupException::forSetupException('No crawler port available');
        }
        
        $resource = @fsockopen('127.0.0.1', $this->currentPort);
        if (!is_resource($resource)) {
            if ($this->crawlerClient === 'firefox') {
                $this->client = Client::createFirefoxClient(null, null, ['port' => $this->currentPort]);
            } else {
                $this->client = Client::createChromeClient(null, null, ['port' => $this->currentPort]);
            }
        } else {
            $this->currentPort++;
            $this->setUpClient();
        }
    }
    
    public function click(string $selector, string $actionMessage, ?string $waitForSelector = null, ?bool $xpath = false, ?int $additionalSleepInMs = 0): void
    {
        $element = $this->getElement($selector, $actionMessage, $waitForSelector, $xpath, $additionalSleepInMs);

        $element->click();
    }
    
    public function getTextFromElement(
        string $selector,
        string $actionMessage,
        ?string $waitForSelector = null,
        ?bool $xpath = false,
        ?int $additionalSleepInMs = 0,
        ?bool $useInnerHTML = false,
        ?bool $useInnerText = false,
        ?string $customAttribute = ''
    ): ?string
    {
        $element = $this->getElement($selector, $actionMessage, $waitForSelector, $xpath, $additionalSleepInMs);

        if ($useInnerHTML) {
            return $element->getAttribute('innerHTML');
        }

        if ($useInnerText) {
            return $element->getAttribute('innerText');
        }

        if ($customAttribute) {
            return $element->getAttribute($customAttribute);
        }

        return $element->getText();
    }
    
    public function getFileFromImage(string $selector, ?string $baseUrl = ''): File
    {
        try {
            $crawler = $this->client->waitFor($selector, 5);
            $url = $crawler->findElement(WebDriverBy::cssSelector($selector))
                ->getAttribute('src');

            if (!str_contains($url, 'http')) {
                $url = $baseUrl . $url;
            }
            
            return $this->downloader->downloadCompanyUrl($url);
        } catch (TimeoutException $e) {
            $exception = CrawlerTimeoutException::forTimoutReached('get profile image', $selector);
            $this->client->takeScreenshot($this->publicPath . '/pantherDebug/' . $exception->getId() . '.jpg');
            throw $exception;
        }
    }
    
    public function enterText(string $selector, string $text, string $actionMessage, ?string $waitForSelector = null, ?bool $xpath = false, ?int $additionalSleepInMs = 0): void
    {
        $element = $this->getElement($selector, $actionMessage, $waitForSelector, $xpath, $additionalSleepInMs);

        $element
            ->clear()
            ->sendKeys($text);
    }

    public function getName(): string
    {
        return self::UNKNOWN_SUPPLIER;
    }

    public function supports(Supplier $supplier): bool
    {
        return $supplier->hasConnectedSupplier() &&
            $supplier->getConnectedSupplier()->getName() === static::getName();
    }

    public function elementExists(string $selector, string $actionMessage, ?string $waitForSelector = null, ?bool $xpath = false, ?int $additionalSleepInMs = 0): void
    {
        $this->getElement($selector, $actionMessage, $waitForSelector, $xpath, $additionalSleepInMs);
    }

    protected function getElement(string $selector, string $actionMessage, ?string $waitForSelector = null, ?bool $xpath = false, ?int $additionalSleepInMs = 0): WebDriverElement
    {
        $elements = $this->getElements($selector, $actionMessage, $waitForSelector, $xpath, $additionalSleepInMs);

        return $elements[0];
    }

    /**
     * @return WebDriverElement[]
     */
    protected function getElements(string $selector, string $actionMessage, ?string $waitForSelector = null, ?bool $xpath = false, ?int $additionalSleepInMs = 0): array
    {
        usleep($additionalSleepInMs * 1000);

        try {
            if (!$waitForSelector) {
                $waitForSelector = $selector;
            }

            $crawler = $this->client->waitFor($waitForSelector, 5);

            if ($xpath) {
                return $crawler->findElements(WebDriverBy::xpath($selector));
            } else {
                return $crawler->findElements(WebDriverBy::cssSelector($selector));
            }
        } catch (NoSuchElementException $e) {
            $exception = SelectorNotFoundException::forSelectorNotFound($actionMessage, $waitForSelector);
            $this->client->takeScreenshot($this->publicPath . '/pantherDebug/' . $exception->getId() . '.jpg');
            throw $exception;
        } catch (TimeoutException $e) {
            $exception = CrawlerTimeoutException::forTimoutReached($actionMessage, $waitForSelector);
            $this->client->takeScreenshot($this->publicPath . '/pantherDebug/' . $exception->getId() . '_' . $additionalSleepInMs . '.jpg');
            throw $exception;
        } catch (HandlerFailedException $e) {
            $exception = CrawlerOtherException::forOtherException($actionMessage, $waitForSelector, $e->getMessage());
            $this->client->takeScreenshot($this->publicPath . '/pantherDebug/' . $exception->getId() . '.jpg');
            throw $exception;
        } catch (Exception $e) {
            $exception = CrawlerOtherException::forOtherException($actionMessage, $waitForSelector, $e->getMessage());
            $this->client->takeScreenshot($this->publicPath . '/pantherDebug/' . $exception->getId() . '.jpg');
            throw $exception;
        }
    }
}
