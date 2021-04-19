<?php

declare(strict_types=1);


namespace App\Services\Pdf;


use App\Entity\Company;
use App\Entity\Data\PdfAlignment;
use App\Entity\Data\PdfFieldDimensions;
use App\Entity\Data\PdfFieldBorder;
use App\Entity\Data\PdfSpecificationType;
use App\Exceptions\Domain\InvalidArgumentException;
use App\Services\CurrentUserProvider;
use DateTime;
use Qipsius\TCPDFBundle\Controller\TCPDFController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use TCPDF;

class PdfPrinterService
{
    private TCPDF $pdf;
    private PdfSpecification $labelSpecification;
    private float $currentX;
    private float $currentY;
    private float $contentStartY;
    private int $currentColumn;
    private int $currentRow;
    private const qr_code_style = [
        'vpadding' => 'auto',
        'hpadding' => 'auto'
    ];
    private TCPDFController $tcpdf;
    private ParameterBagInterface $parameterBag;
    private CurrentUserProvider $currentUserProvider;
    private string $publicPath;
    
    public function __construct(
        TCPDFController $tcpdf,
        ParameterBagInterface $parameterBag,
        CurrentUserProvider $currentUserProvider,
        string $publicPath
    )
    {
        $this->tcpdf = $tcpdf;
        $this->parameterBag = $parameterBag;
        $this->currentUserProvider = $currentUserProvider;
        $this->contentStartY = 0;
        $this->publicPath = $publicPath;
    }
    
    private function ensureThatFolderExists(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
    }
    
    private function pointToMm(float $point): float
    {
        return $point / 2.835;
    }
    
    private function setUpPdf(
        ?string $title = 'Lager im Griff Label'
    ): void
    {
        $pageSize = $this->labelSpecification->getPageSize();
        $orientation = $this->labelSpecification->getOrientation();
        $this->pdf = $this->tcpdf->create($orientation, 'mm', $pageSize);
        $this->contentStartY = 0;
        $this->pdf->SetAutoPageBreak(false);
        $this->pdf->SetTitle($title);
        $this->pdf->setPrintHeader($this->labelSpecification->isPrintHeader());
        $this->pdf->setPrintFooter($this->labelSpecification->isPrintFooter());
        if ($this->labelSpecification->isPrintHeader()) {
            $this->pdf->SetHeaderMargin(3);
        }
        if ($this->labelSpecification->isPrintFooter()) {
            $this->pdf->SetFooterMargin();
        }
        if (!$this->labelSpecification->isPrintHeader() && !$this->labelSpecification->isPrintFooter()) {
            $this->pdf->SetMargins(0, 0, 0, true); // For single label qr code size
        }
    }
    
    private function printBackground()
    {
        $img = $this->labelSpecification->getBackgroundImage();
        
        if ($img) {
            $path = $this->publicPath . '/templates/' . $img;
            $this->pdf->Image($path, 0, 0, 210, 297, '', '', '', true, 300, '', false, false, 0);
        }
    }
    
    private function printHeader(?string $heading = 'Lager im Griff', ?string $text = ''): void
    {
        $this->pdf->SetHeaderData(null, null, $heading, $text);
    }
    
    private function initializePrinter(): void
    {
        $this->currentX = $this->labelSpecification->getLabelStartX();
        $this->currentY = $this->labelSpecification->getLabelStartY();
        $this->currentColumn = 1;
        $this->currentRow = 1;
    }
    
    private function breakLineIfNecessary(): void
    {
        if ($this->currentColumn > $this->labelSpecification->getLabelsPerColumn()) {
            $this->currentX = $this->labelSpecification->getLabelStartX();
            $this->currentY += $this->labelSpecification->getLabelHeight();
            $this->currentColumn = 1;
            $this->currentRow++;
        }
    }
    
    private function breakPageIfNecessary(): void
    {
        if ($this->labelSpecification->getRowsPerPage() && $this->currentRow > $this->labelSpecification->getRowsPerPage()) {
            $this->currentY = $this->labelSpecification->getLabelStartY();
            $this->currentColumn = 1;
            $this->currentRow = 1;
            $this->pdf->AddPage();
            $this->printBackground();
        }
    }
    
    private function goToNextItem(): void
    {
        $this->currentX += $this->labelSpecification->getLabelWidth();
        $this->currentColumn++;
        
        $this->breakLineIfNecessary();
        $this->breakPageIfNecessary();
    }
    
    private function printAllItems(array $data, array $contentTypes): void
    {
        $copies = $this->labelSpecification->getCopies();
        $lastItem = count($data) * $copies;
        $currentItem = 1;
        if ($this->contentStartY) {
            $this->currentY = $this->contentStartY;
        }
        foreach ($data as $index => $content) {
            for ($i = 1; $i <= $copies; $i++) {
                $this->printItem($content, $contentTypes);
                if ($currentItem < $lastItem) {
                    $this->goToNextItem();
                }
                $currentItem++;
            }
        }
    }
    
    private function printFields(array $commonContent, array $commonTypes, array $itemsContent, array $contentTypes): void
    {
        if (count($commonTypes) === 0) {
            $this->printAllItems($itemsContent, $contentTypes);
        }
        
        foreach ($this->labelSpecification->getCommonFieldDimensionDtos() as $index => $field) {
            $this->printField($field, $this->currentX, $this->currentY, $commonContent[$index] ?? '', $commonTypes[$index]);
            if ($field->isStartContentAfterwards()) {
                $this->contentStartY = $this->pdf->GetY();
                $this->printAllItems($itemsContent, $contentTypes);
                $this->currentY = $this->currentY + 5;
            }
        }
    }
    
    private function printItem(array $content, array $contentTypes): void {
        foreach ($this->labelSpecification->getItemFieldDimensionDtos() as $index => $field) {
            $this->printField($field, $this->currentX, $this->currentY, $content[$index] ?? '', $contentTypes[$index]);
        }
    }
    
    private function printContent(
        $content,
        string $contentType,
        PdfFieldBorder $border,
        float $x,
        float $y,
        float $w,
        float $h,
        string $alignment,
        float $fontSize,
        string $bold
    ): float
    {
        switch($contentType) {
            case 'qr':
                if (is_array($content)) {
                    $content = implode(', ', $content);
                }
                if ($border->getValue() === PdfFieldBorder::all()->getValue()) {
                    $this->pdf->Rect($x, $y, $w, $h);
                }
                $this->pdf->write2DBarcode($content, 'QRCODE,M', $x, $y, $w, $h, self::qr_code_style);
                break;
            case 'image':
                if (is_array($content)) {
                    throw InvalidArgumentException::forInvalidElement(implode('; ', $content), 'strings');
                }
                if ($content) {
                    $fitbox = 'CM';
                    if ($alignment === 'L') {
                        $fitbox = 'TL';
                    }
                    if ($alignment === 'R') {
                        $fitbox = 'TR';
                    }
                    $this->pdf->Image($content, $x + 0.5, $y + 0.5, $w - 1, $h - 1, null, '', '', false, 300, '', false, false, 0, $fitbox, false, false);
                }
                if ($border->getValue() === PdfFieldBorder::all()->getValue()) {
                    $this->pdf->Rect($x, $y, $w, $h);
                }
                break;
            default:
                if ($this->labelSpecification->isAutoIncreaseFontSize()) {
                    $fontSize = 128;
                }
                $this->pdf->SetFont("", $bold, $fontSize);
                $borderType = 0;
                if ($border->getValue() === PdfFieldBorder::all()->getValue()) {
                    $borderType = 1;
                }
                if ($border->getValue() === PdfFieldBorder::bottom()->getValue()) {
                    $borderType = "B";
                }
                $valign = $this->labelSpecification->isAutoIncreaseFontSize() ? 'M' : 'T';
                if (is_array($content)) {
                    foreach ($content as $item) {
                        $this->pdf->MultiCell($w, $h, $item, $borderType, $alignment, 0, 1, $x, $y, true, 0, false, true, 0, $valign, true);
                        $y = $y + $this->pointToMm($fontSize) + 1.4;
                    }
                } else if (strstr($content, PHP_EOL)) {
                    $this->pdf->MultiCell($w, $h, $content, $borderType, $alignment, 0, 1, $x, $y, true, 0, false, true, 0, $valign, true);
                } else {
                    if (!$h) {
                        $h = $this->pointToMm($fontSize) + 1.4;
                    }
                    $this->pdf->MultiCell($w, $h, $content, $borderType, $alignment, 0, 0, $x, $y, true, 0, false, true, 0, $valign, true);
                }
                $this->pdf->SetFont("");
                break;
        }
        return $y;
    }
    
    private function printField(PdfFieldDimensions $pdfFieldDimensions, $fieldStartX, $fieldStartY, $content, string $contentType): void
    {
        
        if ($this->labelSpecification->getPdfSpecificationType()->getValue() !== PdfSpecificationType::label()->getValue()) {
            if (!$content && $pdfFieldDimensions->isSkipEmpty()) {
                return;
            }
            $x = $pdfFieldDimensions->getX();
            if ((int) $pdfFieldDimensions->getX() === 0) {
                $x = $pdfFieldDimensions->getX() + $fieldStartX;
            }
            $w = $pdfFieldDimensions->getWidth();
            $h = $pdfFieldDimensions->getHeight();
    
            $y = $this->currentY;
            if ($pdfFieldDimensions->getY() > 0) {
                $y = $pdfFieldDimensions->getY();
            }
    
            if ($x < 0) {
                $x = $this->pdf->getPageWidth() + $x - $w;
            }
            if ((int) $w === 0) {
                $w = $this->pdf->getPageWidth() - (2 * $x);
            }
        } else {
            $x = $pdfFieldDimensions->getX() + $fieldStartX;
            $y = $pdfFieldDimensions->getY() + $fieldStartY;
            $w = $pdfFieldDimensions->getWidth();
            $h = $pdfFieldDimensions->getHeight();
        }
        
        $alignment = "L";
        if ($pdfFieldDimensions->getAlignment()->getValue() === PdfAlignment::center()->getValue()) {
            $alignment = "C";
        }
        if ($pdfFieldDimensions->getAlignment()->getValue() === PdfAlignment::right()->getValue()) {
            $alignment = "R";
        }
        
        $y = $this->printContent(
            $content,
            $contentType,
            $pdfFieldDimensions->getBorder(),
            $x,
            $y,
            $w,
            $h,
            $alignment,
            $pdfFieldDimensions->getFontSize(),
            $pdfFieldDimensions->getFormat()
        );
    
        if ($this->labelSpecification->getPdfSpecificationType()->getValue() !== PdfSpecificationType::label()->getValue()) {
            $this->pdf->SetY($y + $pdfFieldDimensions->getMarginAfterwards());
    
            $currentY = $this->pdf->GetY();
    
            $this->currentX = $x + $pdfFieldDimensions->getWidth();
            $this->currentY = $currentY;
        }
    }
    
    private function createPdf(Company $company, string $fileName): string
    {
        $folder = $this->publicPath . '/companyData/' . $company->getId() . '/pdf/';
        $fullPath = $folder . $fileName;
        $apiPath = 'companyData/' . $company->getId() . '/pdf/' .$fileName;
        $this->ensureThatFolderExists($folder);
        $this->pdf->Output($fullPath, 'F');
        return $apiPath;
    }
    
    public function createPdfDocument(string $fileName, array $data, array $commonData, PdfSpecification $labelSpecification, ?array $contentTypes = null, ?array $commonTypes = null): string
    {
        $company = $this->currentUserProvider->getCompany();
        $this->labelSpecification = $labelSpecification;
        $this->setUpPdf();
        $headerText = sprintf('Etiketten für %s vom %s Uhr', $company->getName(), (new DateTime())->format('d.m.Y H:i:s'));
        $this->printHeader($headerText);
        $this->pdf->AddPage();
        $this->printBackground();
        
        $this->initializePrinter();
        $this->printFields($commonData, $commonTypes, $data, $contentTypes);
        
        return $this->createPdf($company,$fileName);
    }
}
