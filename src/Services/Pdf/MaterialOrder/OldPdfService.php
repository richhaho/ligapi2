<?php

declare(strict_types=1);


namespace App\Services\Pdf\MaterialOrder;


use App\Repository\MaterialOrderRepository;
use App\Services\CurrentUserProvider;
use Qipsius\TCPDFBundle\Controller\TCPDFController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use TCPDF;

abstract class OldPdfService
{
    private ParameterBagInterface $parameterBag;
    private TCPDF $pdf;
    
    public int $smallFontSize = 8;
    public int $middleFontSize = 9;
    public int $paragraphFontSize = 10;
    public int $bigFontSize = 12;
    public int $hugeFontSize = 14;
    public int $titleFontSize = 16;
    
    public int $addressX = 30;
    public int $addressY = 50;
    
    public int $headerInformationFirstColumnX = 130;
    public int $headerInformationSecondColumnX = 165;
    public int $headerInformationY = 50;
    
    public int $titleX = 18;
    public int $titleY = 85;
    
    public array $detailInformationFirstColumnsXs = [18, 30, 83, 95, 140, 160];
    public int $detailInformationY = 102;
    
    public int $tableHeaderTopMargin = 120;
    public int $tableLineMarginX = 13;
    
    public int $tableBodyY = 127;
    public array $tableWidths = [14, 90, 37, 20, 13];
    
    public int $totalY = 175;
    
    public int $companyLineY = 270;
    public int $companyLineMarginX = 10;
    
    public int $firstBankDataX = 5;
    public int $bankDataY = -95;
    
    public int $firstAccountX = 30;
    public int $accountY = -62;
    
    public int $firstSumX = 2;
    public float $secondSumX = 47.5;
    public float $thirdSumX = 65;
    public float $forthSumX = 110.5;
    public int $sumY = -55;
    public int $sumWidth = 40;
    
    public int $invoiceNumberY = -95;
    public int $invoiceNumberX = -88;
    
    private MaterialOrderRepository $materialOrderRepository;
    private TCPDFController $tcpdf;
    protected CurrentUserProvider $currentUserProvider;
    
    public function __construct(
        TCPDFController $tcpdf,
        ParameterBagInterface $parameterBag,
        MaterialOrderRepository $materialOrderRepository,
        CurrentUserProvider $currentUserProvider
    )
    {
        $this->parameterBag = $parameterBag;
        $this->materialOrderRepository = $materialOrderRepository;
        $this->tcpdf = $tcpdf;
        $this->currentUserProvider = $currentUserProvider;
    }
    
    private function setSmallFont()
    {
        $this->pdf->SetFontSize($this->smallFontSize);
    }
    
//    private function setMiddleFont()
//    {
//        $this->pdf->SetFontSize($this->middleFontSize);
//    }
//
//    private function setAccountFont()
//    {
//        $this->pdf->SetFontSize($this->bigFontSize);
//    }
//
//    private function setSumFont()
//    {
//        $this->pdf->SetFontSize($this->hugeFontSize);
//    }
    
    private function setParagraphFont(?bool $bold = false)
    {
        $this->pdf->SetFontSize($this->paragraphFontSize);
        if ($bold) {
            $this->pdf->SetFont('', 'B');
        } else {
            $this->pdf->SetFont('');
        }
    }
    
    private function setTitleFont()
    {
        $this->pdf->SetFontSize($this->titleFontSize);
        $this->pdf->SetFont('', 'B');
    }
    
    protected function ensureThatFolderExists(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
    }
    
    public function setUpPdf(
        ?string $orientation = 'P',
        ?string $unit = 'mm',
        ?string $format = 'A4',
        ?string $title = 'Bestellung'
    ): void
    {
        $this->pdf = $this->tcpdf->create($orientation, $unit, $format);
        $this->pdf->SetTitle($title);
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);
        $this->pdf->AddPage();
        $this->pdf->SetFooterMargin(10);
        $this->pdf->SetHeaderMargin(3);
    }
    
    protected function setLogo(
        string $path,
        int $logoX,
        int $logoY,
        int $logoWidth,
        int $logoHeight,
        ?string $logoAlign = 'C'
    )
    {
        $projectDir = $this->parameterBag->get('kernel.project_dir') . '/public/';
        $logoPath = $projectDir . $path;
        $fitbox = $logoAlign.' ';
        $fitbox[1] = 'T';
        $this->pdf->Image($logoPath, $logoX, $logoY, $logoWidth, $logoHeight, null, '', '', false, 300, '', false, false, 0, $fitbox, false, false);
    }
    
    protected function setRecepientAddress(array $address)
    {
        $this->pdf->SetLeftMargin($this->addressX);
        $this->pdf->SetY($this->addressY);
        foreach ($address as $addressLine) {
            $this->pdf->Write(0, $addressLine, '', 0, 'L', true);
        }
    }
    
    protected function setCompanyInformation(array $headerInformationFirstColumn, array $headerInformationSecondColumn)
    {
        $this->setParagraphFont();
        $this->pdf->SetLeftMargin($this->headerInformationFirstColumnX);
        $this->pdf->SetY($this->headerInformationY);
        foreach ($headerInformationFirstColumn as $infoLine) {
            $this->pdf->Write(0, $infoLine, '', 0, 'L', true);
        }
        $this->pdf->SetLeftMargin($this->headerInformationSecondColumnX);
        $this->pdf->SetY($this->headerInformationY);
        foreach ($headerInformationSecondColumn as $infoLine) {
            $this->pdf->Write(0, $infoLine, '', 0, 'L', true);
        }
        $this->pdf->SetLeftMargin(0);
    }
    
    protected function setTitle(string $type, ?string $highlighted = null, string $subtitle = null)
    {
        $this->setTitleFont();
        $this->pdf->SetLeftMargin($this->titleX);
        $this->pdf->SetY($this->titleY);
        $this->pdf->Write(0, $type);
        if ($highlighted) {
            $this->pdf->SetTextColor(255,0,0);
            $this->pdf->Write(0, $highlighted);
            $this->pdf->SetTextColor(0,0,0);
        }
        $this->pdf->Ln(12);
        $this->setParagraphFont();
        $this->pdf->Write(0, $subtitle);
        $this->pdf->Ln();
        $this->pdf->SetLeftMargin(0);
    }
    
    protected function setBodyText(string $text)
    {
        $this->pdf->SetLeftMargin($this->titleX);
        $this->pdf->Ln();
        $this->pdf->Ln();
        $this->setParagraphFont();
        $this->pdf->Write(0, $text);
        $this->pdf->SetLeftMargin(0);
    }
    
    protected function setDetailInformation(array $detailInformation)
    {
        foreach ($detailInformation as $index => $detailInformationColumn) {
            $this->pdf->SetY($this->detailInformationY);
            foreach ($detailInformationColumn as $detailInformationRow) {
                $this->pdf->SetX($this->detailInformationFirstColumnsXs[$index]);
                $this->pdf->Write(0, $detailInformationRow, '', 0, 'L', true);
            }
        }
    }
    
    protected function setTableHeader(array $tableHeaderInformation)
    {
        $this->setParagraphFont(true);
        $this->pdf->SetY($this->tableHeaderTopMargin);
        
        $this->pdf->SetX($this->titleX);
        $this->pdf->Cell($this->tableWidths[0], 0, $tableHeaderInformation[0], 0, 0, 'R');
        $this->pdf->Cell($this->tableWidths[1], 0, $tableHeaderInformation[1], 0, 0, 'L');
        $this->pdf->Cell($this->tableWidths[2], 0, $tableHeaderInformation[2], 0, 0, 'L');
        $this->pdf->Cell($this->tableWidths[3], 0, $tableHeaderInformation[3], 0, 0, 'R');
        $this->pdf->Cell($this->tableWidths[4], 0, $tableHeaderInformation[4], 0, 0, 'L');
        $this->setParagraphFont();
        
        $lineXStart = $this->tableLineMarginX;
        $lineXEnd = $this->pdf->getPageWidth() - $this->tableLineMarginX;
        $lineY = $this->tableHeaderTopMargin + 6;
    
        $this->pdf->Line($lineXStart, $lineY, $lineXEnd, $lineY);
    }
    
    protected function setMaterialOrderPositions(array $materialOrderPositions)
    {
        $this->pdf->SetY($this->tableBodyY);
        $this->pdf->SetLeftMargin($this->titleX);
        
        foreach ($materialOrderPositions as $materialOrderPosition) {
            $this->pdf->Cell($this->tableWidths[0], 0, $materialOrderPosition['number'], 0, 0, 'R');
            $this->pdf->Cell($this->tableWidths[1], 0, $materialOrderPosition['name'], 0, 0, 'L');
            $this->pdf->Cell($this->tableWidths[2], 0, $materialOrderPosition['orderNumber'], 0, 0, 'L');
            $this->pdf->Cell($this->tableWidths[3], 0, $materialOrderPosition['amount'], 0, 0, 'R');
            $this->pdf->Cell($this->tableWidths[4], 0, $materialOrderPosition['unit'], 0, 1, 'L');
        }
    }
    
    protected function setCompanyLine($companyLine)
    {
        $this->pdf->SetY($this->companyLineY);
        $this->pdf->SetX($this->companyLineMarginX);
        $this->setSmallFont();
        $cellWidth = $this->pdf->getPageWidth() - 2 * $this->companyLineMarginX;
        $this->pdf->Cell($cellWidth, 0, $companyLine, 0, 0, 'C');
        $this->setParagraphFont();
    }
    
    protected function createPdf(string $relativeFolder, string $fileName): string
    {
        $projectDir = $this->parameterBag->get('kernel.project_dir') . '/public/';
        $fullPath = $projectDir . $relativeFolder . $fileName;
        $this->ensureThatFolderExists($projectDir . $relativeFolder);
        $this->pdf->Output($fullPath, 'F');
        return $relativeFolder . $fileName;
    }
    
}
